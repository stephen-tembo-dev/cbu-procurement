<?php

use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\Procurement\PurchaseRequisition;
use App\Models\UnitOfMeasure;
use App\Services\ProcurementService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int    $prId;
    public int    $supplierId   = 0;
    public string $deliveryDate = '';
    public array  $items        = [];
    public ?string $flash       = null;
    public ?string $flashType   = null;

    public function mount(int $id): void
    {
        $user = auth()->user();
        abort_unless($user->hasRole('procurement'), 403);

        $pr = app(PurchaseRequisitionRepositoryInterface::class)->findWithFullTrail($id);
        abort_if(! $pr, 404);
        abort_unless(in_array($pr->status, ['pending_procurement', 'pending_vc_payment', 'pending_payment']), 403);
        abort_if($pr->purchaseOrder !== null, 403);

        $this->prId  = $id;
        // Only items not fully issued from stores go onto the PO.
        $this->items = $pr->items
            ->filter(fn ($item) => ! $item->isFullyIssued())
            ->map(fn ($item) => [
                'purchase_requisition_item_id' => $item->id,
                'description'     => $item->description,
                'quantity'        => (string) $item->unfulfilledQuantity(),
                'unit_of_measure' => $item->unit_of_measure ?? '',
                'unit_price'      => '',
                'total_price'     => '0.00',
            ])->values()->toArray();
    }

    #[Computed]
    public function pr(): PurchaseRequisition
    {
        return app(PurchaseRequisitionRepositoryInterface::class)->findWithFullTrail($this->prId);
    }

    #[Computed]
    public function units(): array
    {
        return UnitOfMeasure::active()->get()->mapWithKeys(fn ($u) => [$u->abbreviation => $u->label()])->toArray();
    }

    #[Computed]
    public function suppliers(): array
    {
        return \App\Models\Supplier::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function updatedItems(mixed $value, string $key): void
    {
        if (! str_contains($key, '.')) {
            return;
        }

        [$idx, $field] = explode('.', $key, 2);

        if (! in_array($field, ['quantity', 'unit_price'], true)) {
            return;
        }

        $i     = (int) $idx;
        $qty   = (float) ($this->items[$i]['quantity'] ?? 0);
        $price = (float) ($this->items[$i]['unit_price'] ?? 0);
        $this->items[$i]['total_price'] = number_format($qty * $price, 2, '.', '');
    }

    public function submit(): void
    {
        $this->validate([
            'supplierId'              => 'required|integer|min:1|exists:suppliers,id',
            'deliveryDate'            => 'required|date|after:today',
            'items'                   => 'required|array|min:1',
            'items.*.description'     => 'required|string|max:255',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit_of_measure' => 'nullable|string|max:50',
            'items.*.unit_price'      => 'required|numeric|min:0.01',
            'items.*.total_price'     => 'required|numeric|min:0',
        ]);

        $pr   = $this->pr;
        $user = auth()->user();

        try {
            $itemsData = array_map(fn ($i) => [
                'purchase_requisition_item_id' => $i['purchase_requisition_item_id'],
                'description'     => $i['description'],
                'quantity'        => (float) $i['quantity'],
                'unit_of_measure' => $i['unit_of_measure'] ?: null,
                'unit_price'      => (float) $i['unit_price'],
                'total_price'     => (float) $i['total_price'],
            ], $this->items);

            app(ProcurementService::class)->createPurchaseOrder(
                $pr,
                $this->supplierId,
                $itemsData,
                $user,
                $this->deliveryDate,
            );

            session()->flash('success', 'Purchase Order created successfully.');
            $this->redirect(route('requisitions.show', $this->prId));
        } catch (RuntimeException $e) {
            $this->flash     = $e->getMessage();
            $this->flashType = 'error';
        }
    }

    public function dismissFlash(): void
    {
        $this->flash     = null;
        $this->flashType = null;
    }
};
