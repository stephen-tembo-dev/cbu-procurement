<?php

use App\Models\ItemCategory;
use App\Models\StockItem;
use App\Models\UnitOfMeasure;
use App\Services\StoresService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public string $activeSection = 'stock-items';
    public string $activeTab     = 'all'; // 'all' | 'recurring' | 'due'

    // Create / edit modal
    public bool   $showModal     = false;
    public ?int   $editingId     = null;

    // Core item fields
    public string $stockCode       = '';
    public string $description     = '';
    public string $category        = '';
    public bool   $isStocked       = false;
    public string $quantityOnHand  = '0';
    public string $reorderLevel    = '0';
    public string $unitOfMeasure   = '';
    public bool   $isActive        = true;

    // Recurring fields
    public bool   $isRecurring          = false;
    public string $frequencyPreset      = '30';  // preset days value or 'custom'
    public string $reorderFrequencyDays = '30';  // actual stored value
    public string $lastReorderDate      = '';

    // Record-reorder modal
    public bool   $showReorderModal  = false;
    public ?int   $reorderingItemId  = null;
    public string $reorderDate       = '';

    // Inventory movement history
    public bool   $showHistoryModal  = false;
    public ?int   $historyItemId     = null;

    // Flash
    public ?string $flash     = null;
    public string  $flashType = 'success';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'stores']), 403);
    }

    // ── Catalog lookups ────────────────────────────────────────────────────

    #[Computed]
    public function catalogCategories(): array
    {
        return ItemCategory::active()->pluck('name')->toArray();
    }

    #[Computed]
    public function catalogUnits(): array
    {
        return UnitOfMeasure::active()->get()->mapWithKeys(fn ($u) => [$u->abbreviation => $u->label()])->toArray();
    }

    // ── Tab switching ──────────────────────────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    // ── Filament table ─────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'recurring' => $this->recurringTable($table),
            'due'       => $this->dueTable($table),
            default     => $this->allItemsTable($table),
        };
    }

    private function allItemsTable(Table $table): Table
    {
        return $table
            ->query(StockItem::latest())
            ->columns([
                TextColumn::make('stock_code')
                    ->label('Code')->badge()->color('gray')->searchable(),
                TextColumn::make('description')
                    ->searchable()->wrap(),
                TextColumn::make('category')->default('—'),
                IconColumn::make('is_stocked')->label('Stocked')->boolean(),
                IconColumn::make('is_recurring')->label('Recurring')->boolean(),
                TextColumn::make('quantity_on_hand')->label('Qty on Hand')->numeric(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->actions($this->itemActions())
            ->emptyStateHeading('No stock items found')
            ->emptyStateDescription('Add your first stock item to get started.');
    }

    private function recurringTable(Table $table): Table
    {
        return $table
            ->query(StockItem::recurring()->latest())
            ->columns([
                TextColumn::make('stock_code')
                    ->label('Code')->badge()->color('gray')->searchable(),
                TextColumn::make('description')->searchable()->wrap(),
                TextColumn::make('reorder_frequency_days')
                    ->label('Frequency')
                    ->formatStateUsing(fn ($state) => $this->formatFrequency((int) $state)),
                TextColumn::make('last_reorder_date')
                    ->label('Last Reordered')
                    ->date('d M Y')
                    ->placeholder('Never'),
                TextColumn::make('next_reorder_date')
                    ->label('Next Due')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : null),
                TextColumn::make('quantity_on_hand')->label('Qty on Hand')->numeric(),
            ])
            ->actions([
                ...$this->itemActions(),
                Action::make('recordReorder')
                    ->label('Record Reorder')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(fn (StockItem $record) => $this->openReorderModal($record->id)),
            ])
            ->emptyStateHeading('No recurring items')
            ->emptyStateDescription('Mark a stock item as recurring to manage its reorder schedule.');
    }

    private function dueTable(Table $table): Table
    {
        return $table
            ->query(
                StockItem::dueForReorder()
                    ->orWhere(fn ($q) => $q->where('is_active', true)
                        ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                        ->where('quantity_on_hand', '>', 0)
                    )
                    ->latest()
            )
            ->columns([
                TextColumn::make('stock_code')
                    ->label('Code')->badge()->color('gray')->searchable(),
                TextColumn::make('description')->searchable()->wrap(),
                TextColumn::make('quantity_on_hand')
                    ->label('Qty on Hand')
                    ->numeric()
                    ->color(fn ($state, $record) => (float) $state <= (float) $record->reorder_level ? 'danger' : null),
                TextColumn::make('reorder_level')->label('Reorder Level')->numeric(),
                TextColumn::make('next_reorder_date')
                    ->label('Schedule Due')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->color('danger'),
                TextColumn::make('last_reorder_date')
                    ->label('Last Reordered')
                    ->date('d M Y')
                    ->placeholder('Never'),
            ])
            ->actions([
                ...$this->itemActions(),
                Action::make('recordReorder')
                    ->label('Record Reorder')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (StockItem $record) => $record->is_recurring)
                    ->action(fn (StockItem $record) => $this->openReorderModal($record->id)),
            ])
            ->emptyStateHeading('No items due for reorder')
            ->emptyStateDescription('All recurring items are on schedule and stock levels are healthy.');
    }

    private function itemActions(): array
    {
        return [
            Action::make('history')
                ->label('History')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->action(fn (StockItem $record) => $this->openHistory($record->id)),
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')
                ->action(fn (StockItem $record) => $this->openEdit($record->id)),
            Action::make('toggleActive')
                ->label(fn (StockItem $record) => $record->is_active ? 'Deactivate' : 'Activate')
                ->icon(fn (StockItem $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (StockItem $record) => $record->is_active ? 'danger' : 'success')
                ->action(fn (StockItem $record) => $this->toggleActive($record->id)),
        ];
    }

    public function openHistory(int $id): void
    {
        $this->historyItemId = StockItem::findOrFail($id)->id;
        $this->showHistoryModal = true;
    }

    public function closeHistory(): void
    {
        $this->showHistoryModal = false;
        $this->historyItemId = null;
    }

    // ── Create / edit ──────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->editingId           = null;
        $this->stockCode           = '';
        $this->description         = '';
        $this->category            = '';
        $this->isStocked           = false;
        $this->quantityOnHand      = '0';
        $this->reorderLevel        = '0';
        $this->unitOfMeasure       = '';
        $this->isActive            = true;
        $this->isRecurring         = false;
        $this->frequencyPreset     = '30';
        $this->reorderFrequencyDays = '30';
        $this->lastReorderDate     = '';
        $this->resetErrorBag();
        $this->showModal           = true;
    }

    public function openEdit(int $id): void
    {
        $item = StockItem::findOrFail($id);

        $this->editingId           = $item->id;
        $this->stockCode           = $item->stock_code;
        $this->description         = $item->description;
        $this->category            = $item->category ?? '';
        $this->isStocked           = (bool) $item->is_stocked;
        $this->quantityOnHand      = (string) $item->quantity_on_hand;
        $this->reorderLevel        = (string) $item->reorder_level;
        $this->unitOfMeasure       = $item->unit_of_measure ?? '';
        $this->isActive            = (bool) $item->is_active;
        $this->isRecurring         = (bool) $item->is_recurring;
        $this->lastReorderDate     = $item->last_reorder_date?->toDateString() ?? '';

        $freq = (int) $item->reorder_frequency_days;
        $presets = [7, 14, 30, 60, 91, 182, 365];
        $this->reorderFrequencyDays = $freq ? (string) $freq : '30';
        $this->frequencyPreset      = in_array($freq, $presets) ? (string) $freq : 'custom';

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    public function updatedFrequencyPreset(string $value): void
    {
        if ($value !== 'custom') {
            $this->reorderFrequencyDays = $value;
        }
    }

    public function save(): void
    {
        $rules = [
            'stockCode'      => [
                'required', 'string', 'max:50',
                Rule::unique('stock_items', 'stock_code')->ignore($this->editingId),
            ],
            'description'    => 'required|string|max:255',
            'quantityOnHand' => 'numeric|min:0',
            'reorderLevel'   => 'numeric|min:0',
            'isStocked'      => 'boolean',
            'isActive'       => 'boolean',
            'isRecurring'    => 'boolean',
        ];

        if ($this->isRecurring) {
            $rules['reorderFrequencyDays'] = 'required|integer|min:1|max:3650';
            $rules['lastReorderDate']      = 'nullable|date';
        }

        $this->validate($rules);

        $data = [
            'stock_code'       => $this->stockCode,
            'description'      => $this->description,
            'category'         => $this->category ?: null,
            'is_stocked'       => $this->isStocked,
            'quantity_on_hand' => $this->quantityOnHand,
            'reorder_level'    => $this->reorderLevel,
            'unit_of_measure'  => $this->unitOfMeasure ?: null,
            'is_active'        => $this->isActive,
            'is_recurring'     => $this->isRecurring,
        ];

        if ($this->isRecurring) {
            $lastDate = $this->lastReorderDate ? \Carbon\Carbon::parse($this->lastReorderDate) : null;
            $nextDate = $lastDate
                ? $lastDate->copy()->addDays((int) $this->reorderFrequencyDays)
                : null;

            $data['reorder_frequency_days'] = (int) $this->reorderFrequencyDays;
            $data['last_reorder_date']      = $lastDate?->toDateString();
            $data['next_reorder_date']      = $nextDate?->toDateString();
        } else {
            $data['reorder_frequency_days'] = null;
            $data['last_reorder_date']      = null;
            $data['next_reorder_date']      = null;
        }

        if ($this->editingId) {
            StockItem::findOrFail($this->editingId)->update($data);
            $this->flash = 'Stock item updated successfully.';
        } else {
            StockItem::create($data);
            $this->flash = 'Stock item created successfully.';
        }

        $this->flashType = 'success';
        $this->closeModal();
        $this->resetTable();
    }

    public function toggleActive(int $id): void
    {
        $item = StockItem::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
        $this->flash     = 'Stock item ' . ($item->is_active ? 'activated' : 'deactivated') . '.';
        $this->flashType = 'success';
        $this->resetTable();
    }

    // ── Record reorder ─────────────────────────────────────────────────────

    public function openReorderModal(int $id): void
    {
        $this->reorderingItemId = $id;
        $this->reorderDate      = now()->toDateString();
        $this->resetErrorBag();
        $this->showReorderModal = true;
    }

    public function closeReorderModal(): void
    {
        $this->showReorderModal = false;
        $this->reorderingItemId = null;
    }

    public function confirmReorder(): void
    {
        $this->validate([
            'reorderDate' => 'required|date|before_or_equal:today',
        ]);

        $item    = StockItem::findOrFail($this->reorderingItemId);
        $service = app(StoresService::class);
        $service->recordReorder($item, auth()->user(), \Carbon\Carbon::parse($this->reorderDate));

        $next = $item->fresh()->next_reorder_date;
        $this->flash     = "Reorder recorded for {$item->stock_code}."
            . ($next ? ' Next due: ' . $next->format('d M Y') . '.' : '');
        $this->flashType = 'success';
        $this->closeReorderModal();
        $this->resetTable();
    }

    // ── Computed ───────────────────────────────────────────────────────────

    #[Computed]
    public function dueCount(): int
    {
        return StockItem::dueForReorder()->count()
            + StockItem::where('is_active', true)
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0)
                ->count();
    }

    #[Computed]
    public function recurringCount(): int
    {
        return StockItem::recurring()->count();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function dismissFlash(): void
    {
        $this->flash = null;
    }

    private function formatFrequency(int $days): string
    {
        return match ($days) {
            7   => 'Weekly',
            14  => 'Bi-weekly',
            30  => 'Monthly',
            60  => 'Every 2 months',
            91  => 'Quarterly',
            182 => 'Bi-annually',
            365 => 'Annually',
            default => "Every {$days} days",
        };
    }
};
