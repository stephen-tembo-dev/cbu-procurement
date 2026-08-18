<?php

use App\Contracts\CostCentreRepositoryInterface;
use App\Models\ItemCategory;
use App\Models\StockItem;
use App\Models\UnitOfMeasure;
use App\Services\PurchaseRequisitionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $type           = 'product';
    public ?int   $costCentreId   = null;
    public string $notes          = '';
    public string $serviceDate    = '';

    public array $items = [
        [
            'description'           => '',
            'quantity'              => '1',
            'unit_of_measure'       => '',
            'unit_price_estimated'  => '0',
            'category'              => '',
            'stock_item_id'         => null,
            'manual_entry'          => false,
        ],
    ];

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
        $deptId = auth()->user()->department_id;

        if (! $deptId) {
            return [];
        }

        return app(CostCentreRepositoryInterface::class)
            ->findByDepartment($deptId)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * All active stock items grouped by category.
     * Shape: ['Office Supplies' => [['id'=>1,'label'=>'...','description'=>'...','unit_of_measure'=>'...'], ...], ...]
     */
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

    /**
     * Reset stock link and description when category changes so the previous
     * selection doesn't carry over to an unrelated category's catalog.
     */
    public function onCategoryChanged(int $index): void
    {
        $this->items[$index]['stock_item_id']  = null;
        $this->items[$index]['manual_entry']   = false;
        $this->items[$index]['description']    = '';
        $this->items[$index]['unit_of_measure'] = '';
    }

    /**
     * Called when the user picks from the catalog dropdown.
     * $value is the StockItem id, or '__none__' when "Not listed" is chosen.
     */
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
            'type'                             => 'required|in:product,service',
            'costCentreId'                     => 'required|exists:cost_centres,id',
            'items'                            => 'required|array|min:1',
            'items.*.description'              => 'required|string|max:255',
            'items.*.quantity'                 => 'required|numeric|min:0.01',
            'items.*.unit_price_estimated'     => 'required|numeric|min:0',
            'serviceDate'                      => 'required_if:type,service|nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'costCentreId.required' => 'Please select a cost centre.',
            'items.*.description.required' => 'Item description is required.',
            'items.*.quantity.required'    => 'Item quantity is required.',
            'serviceDate.required_if'      => 'Service date is required for service requisitions.',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $data = [
            'type'           => $this->type,
            'cost_centre_id' => $this->costCentreId,
            'notes'          => $this->notes ?: null,
            'items'          => $this->items,
        ];

        if ($this->type === 'service' && $this->serviceDate) {
            $data['service_date'] = $this->serviceDate;
        }

        $pr = app(PurchaseRequisitionService::class)->createAndSubmit($data, auth()->user());

        session()->flash('success', "Requisition {$pr->reference_no} submitted successfully.");
        $this->redirect(route('requisitions.show', $pr->id), navigate: true);
    }
};
