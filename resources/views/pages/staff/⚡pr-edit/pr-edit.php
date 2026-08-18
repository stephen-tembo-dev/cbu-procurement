<?php

use App\Contracts\CostCentreRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\ItemCategory;
use App\Models\StockItem;
use App\Models\UnitOfMeasure;
use App\Services\PurchaseRequisitionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public int    $prId;
    public string $type         = 'product';
    public ?int   $costCentreId = null;
    public string $notes        = '';
    public array  $items        = [];

    public function mount(int $id): void
    {
        $this->prId = $id;

        $pr   = app(PurchaseRequisitionRepositoryInterface::class)->findById($id);
        $user = auth()->user();

        abort_if(! $pr, 404);

        abort_unless(
            $user->can('pr.edit') && ($user->hasRole('admin') || $pr->requester_id === $user->id),
            403
        );

        abort_unless(in_array($pr->status, ['draft', 'pending_hod'], true), 403);

        $this->type         = $pr->type;
        $this->costCentreId = $pr->cost_centre_id;
        $this->notes        = $pr->notes ?? '';
        $this->items        = $pr->items->map(fn ($item) => [
            'description'          => $item->description,
            'quantity'             => (string) $item->quantity,
            'unit_of_measure'      => $item->unit_of_measure ?? '',
            'unit_price_estimated' => (string) $item->unit_price_estimated,
            'category'             => $item->category ?? '',
            'stock_item_id'        => $item->stock_item_id,
            'manual_entry'         => $item->stock_item_id === null,
        ])->toArray();
    }

    #[Computed]
    public function pr()
    {
        return app(PurchaseRequisitionRepositoryInterface::class)->findById($this->prId);
    }

    #[Computed]
    public function categories(): array
    {
        return ItemCategory::active()->pluck('name')->toArray();
    }

    #[Computed]
    public function units(): array
    {
        return UnitOfMeasure::active()->get()->map->label()->toArray();
    }

    #[Computed]
    public function costCentres(): array
    {
        $deptId = $this->pr->department_id;

        return app(CostCentreRepositoryInterface::class)
            ->findByDepartment($deptId)
            ->pluck('name', 'id')
            ->toArray();
    }

    #[Computed]
    public function stockCatalog(): array
    {
        return StockItem::where('is_active', true)
            ->orderBy('stock_code')
            ->get(['id', 'stock_code', 'description', 'category', 'unit_of_measure'])
            ->groupBy('category')
            ->map(fn ($items) => $items->map(fn ($item) => [
                'id'              => $item->id,
                'label'           => "[{$item->stock_code}] {$item->description}",
                'description'     => $item->description,
                'unit_of_measure' => $item->unit_of_measure ?? '',
            ])->values()->toArray())
            ->toArray();
    }

    public function onCategoryChanged(int $index): void
    {
        $this->items[$index]['stock_item_id']   = null;
        $this->items[$index]['manual_entry']    = false;
        $this->items[$index]['description']     = '';
        $this->items[$index]['unit_of_measure'] = '';
    }

    public function onStockItemSelected(int $index, string $value): void
    {
        if ($value === '' || $value === '__none__') {
            $this->items[$index]['stock_item_id']   = null;
            $this->items[$index]['manual_entry']    = ($value === '__none__');
            $this->items[$index]['description']     = '';
            $this->items[$index]['unit_of_measure'] = '';
            return;
        }

        $stock = StockItem::find((int) $value);
        if (! $stock) {
            return;
        }

        $this->items[$index]['stock_item_id']   = $stock->id;
        $this->items[$index]['manual_entry']    = false;
        $this->items[$index]['description']     = $stock->description;
        $this->items[$index]['unit_of_measure'] = $stock->unit_of_measure ?? '';
    }

    public function computedTotal(): float
    {
        return collect($this->items)->sum(
            fn ($item) => (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price_estimated'] ?? 0)
        );
    }

    public function addItem(): void
    {
        $this->items[] = [
            'description'          => '',
            'quantity'             => '1',
            'unit_of_measure'      => '',
            'unit_price_estimated' => '0',
            'category'             => '',
            'stock_item_id'        => null,
            'manual_entry'         => false,
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    protected function rules(): array
    {
        return [
            'costCentreId'                     => 'required|exists:cost_centres,id',
            'items'                            => 'required|array|min:1',
            'items.*.description'              => 'required|string|max:255',
            'items.*.quantity'                 => 'required|numeric|min:0.01',
            'items.*.unit_price_estimated'     => 'required|numeric|min:0',
        ];
    }

    protected function messages(): array
    {
        return [
            'costCentreId.required'        => 'Please select a cost centre.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.quantity.required'    => 'Item quantity is required.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        app(PurchaseRequisitionService::class)->update(
            $this->prId,
            [
                'cost_centre_id' => $this->costCentreId,
                'notes'          => $this->notes ?: null,
                'items'          => $this->items,
            ],
            auth()->user()
        );

        session()->flash('success', 'Requisition updated successfully.');
        $this->redirect(route('requisitions.show', $this->prId), navigate: true);
    }
};
