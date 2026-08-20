<?php

use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisitionItem;
use App\Models\StockItem;
use App\Services\ProcurementService;
use App\Services\StoresService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int     $poId;
    public ?string $flash     = null;
    public ?string $flashType = null;

    // Goods receipt form
    public bool   $showReceiveForm   = false;
    public array  $receiveQuantities = [];
    public string $receiveNote       = '';

    // Stock catalog panel (shown after goods receipt for unlinked items)
    public bool   $showCatalogPanel = false;
    public array  $unlinkedPrItems  = [];   // [{pr_item_id, description, category, unit, qty}]
    public array  $catalogEntries   = [];   // [pr_item_id => {skip, stock_code, is_stocked, reorder_level}]

    public function mount(int $id): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['procurement', 'stores', 'auditor', 'admin', 'vc', 'bursar']),
            403
        );

        abort_unless(PurchaseOrder::where('id', $id)->exists(), 404);

        $this->poId = $id;

        if (session()->has('success')) {
            $this->flash     = session('success');
            $this->flashType = 'success';
        }
    }

    #[Computed]
    public function po(): PurchaseOrder
    {
        return PurchaseOrder::with([
            'supplier',
            'items',
            'payment.vcApprover',
            'payment.bursarConfirmer',
            'purchaseRequisition.department',
            'purchaseRequisition.requester',
            'purchaseRequisition.costCentre',
            'purchaseRequisition.items.stockItem',
        ])->findOrFail($this->poId);
    }

    public function issuePo(): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('procurement'), 403);

        $po = $this->po;

        try {
            app(ProcurementService::class)->issuePurchaseOrder($po, $user);
            unset($this->po);
            $this->flash     = 'Purchase Order issued to supplier successfully.';
            $this->flashType = 'success';
        } catch (RuntimeException $e) {
            $this->flash     = $e->getMessage();
            $this->flashType = 'error';
        }
    }

    // ── Goods receipt ────────────────────────────────────────────────────────

    public function openReceiveForm(): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('stores'), 403);

        $po = $this->po;
        abort_unless(in_array($po->status, ['issued', 'partially_delivered']), 403);

        $this->receiveQuantities = $po->items
            ->mapWithKeys(fn ($item) => [$item->id => ''])
            ->toArray();
        $this->receiveNote     = '';
        $this->showReceiveForm = true;
    }

    public function cancelReceiveForm(): void
    {
        $this->showReceiveForm   = false;
        $this->receiveQuantities = [];
        $this->receiveNote       = '';
    }

    public function submitGoodsReceipt(): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('stores'), 403);

        $po = $this->po;
        abort_unless(in_array($po->status, ['issued', 'partially_delivered']), 403);

        $rules = [];
        foreach ($po->items as $item) {
            $remaining = (float) $item->quantity - (float) ($item->quantity_received ?? 0);
            $rules["receiveQuantities.{$item->id}"] = "nullable|numeric|min:0|max:{$remaining}";
        }
        $this->validate($rules, [
            'receiveQuantities.*.numeric' => 'Quantities must be numbers.',
            'receiveQuantities.*.min'     => 'Quantity cannot be negative.',
            'receiveQuantities.*.max'     => 'Quantity exceeds outstanding amount for that line item.',
        ]);

        $anyReceived = collect($this->receiveQuantities)->filter(fn ($v) => (float) $v > 0)->isNotEmpty();
        if (! $anyReceived) {
            $this->addError('receiveQuantities', 'Enter a received quantity for at least one item.');
            return;
        }

        $allFulfilled = app(StoresService::class)->recordGoodsReceipt($po, $this->receiveQuantities, now(), auth()->user());
        $message = $allFulfilled
            ? 'All items received. Purchase Order marked as fully delivered.'
            : 'Goods receipt recorded. Purchase Order is partially delivered.';

        // Check for PR items with no stock link — offer stores the chance to catalog them
        $pr = $po->purchaseRequisition;
        if ($pr) {
            $pr->loadMissing('items');
            $unlinked = $pr->items->whereNull('stock_item_id')->values();

            if ($unlinked->isNotEmpty()) {
                $this->unlinkedPrItems = $unlinked->map(fn ($item) => [
                    'pr_item_id'  => $item->id,
                    'description' => $item->description,
                    'category'    => $item->category ?? '',
                    'unit'        => $item->unit_of_measure ?? '',
                    'qty'         => (float) $item->quantity,
                ])->toArray();

                $this->catalogEntries = collect($this->unlinkedPrItems)
                    ->keyBy('pr_item_id')
                    ->map(fn () => [
                        'skip'          => false,
                        'stock_code'    => '',
                        'is_stocked'    => true,
                        'reorder_level' => '0',
                    ])->toArray();

                $this->showCatalogPanel = true;
            }
        }

        unset($this->po);
        $this->showReceiveForm   = false;
        $this->receiveQuantities = [];
        $this->receiveNote       = '';
        $this->flash             = $message;
        $this->flashType         = 'success';
    }

    // ── Stock catalog panel ──────────────────────────────────────────────────

    public function saveCatalogEntries(): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('stores'), 403);

        // Build validation rules only for entries not being skipped
        $rules = [];
        foreach ($this->catalogEntries as $prItemId => $entry) {
            if ($entry['skip']) {
                continue;
            }
            $rules["catalogEntries.{$prItemId}.stock_code"]    = 'required|string|max:50|unique:stock_items,stock_code';
            $rules["catalogEntries.{$prItemId}.reorder_level"] = 'nullable|numeric|min:0';
        }

        if ($rules) {
            $this->validate($rules, [
                'catalogEntries.*.stock_code.required' => 'A stock code is required.',
                'catalogEntries.*.stock_code.unique'   => 'This stock code already exists.',
                'catalogEntries.*.reorder_level.numeric' => 'Reorder level must be a number.',
            ]);
        }

        DB::transaction(function () {
            foreach ($this->catalogEntries as $prItemId => $entry) {
                if ($entry['skip']) {
                    continue;
                }

                $prItemData = collect($this->unlinkedPrItems)->firstWhere('pr_item_id', (int) $prItemId);
                if (! $prItemData) {
                    continue;
                }

                $isStocked = (bool) $entry['is_stocked'];

                $stock = StockItem::create([
                    'stock_code'       => strtoupper(trim($entry['stock_code'])),
                    'description'      => $prItemData['description'],
                    'category'         => $prItemData['category'] ?: null,
                    'unit_of_measure'  => $prItemData['unit'] ?: null,
                    'is_stocked'       => $isStocked,
                    'is_active'        => true,
                    // Add received qty to on-hand only if it will be tracked in stock
                    'quantity_on_hand' => $isStocked ? $prItemData['qty'] : 0,
                    'reorder_level'    => (float) ($entry['reorder_level'] ?? 0),
                ]);

                // Link the PR item so future requests use the catalog
                PurchaseRequisitionItem::where('id', (int) $prItemId)
                    ->update(['stock_item_id' => $stock->id]);
            }
        });

        $this->showCatalogPanel = false;
        $this->unlinkedPrItems  = [];
        $this->catalogEntries   = [];
        unset($this->po);

        $this->flash     = 'Stock catalog updated. Items have been linked to their new stock records.';
        $this->flashType = 'success';
    }

    public function dismissCatalogPanel(): void
    {
        $this->showCatalogPanel = false;
        $this->unlinkedPrItems  = [];
        $this->catalogEntries   = [];
    }

    public function dismissFlash(): void
    {
        $this->flash     = null;
        $this->flashType = null;
    }
};
