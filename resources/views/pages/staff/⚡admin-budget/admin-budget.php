<?php

use App\Models\CostCentre;
use App\Models\Finance\BudgetAllocation;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public string $activeSection = 'budget';

    // Fiscal year filter
    public int $fiscalYear;

    // Modal state
    public bool  $showModal     = false;
    public ?int  $editingId     = null;

    // Form fields
    public int    $costCentreId    = 0;
    public string $modalFiscalYear = '';
    public string $allocatedAmount = '';

    // Flash
    public ?string $flash     = null;
    public string  $flashType = 'success';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $this->fiscalYear = (int) date('Y');
    }

    public function table(Table $table): Table
    {
        $year = $this->fiscalYear;

        return $table
            ->query(
                BudgetAllocation::with(['costCentre.department'])
                    ->where('fiscal_year', $year)
                    ->latest()
            )
            ->columns([
                TextColumn::make('costCentre.code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('costCentre.name')
                    ->label('Cost Centre')
                    ->searchable(),
                TextColumn::make('costCentre.department.name')
                    ->label('Department'),
                TextColumn::make('fiscal_year')
                    ->label('Year'),
                TextColumn::make('allocated_amount')
                    ->label('Allocated')
                    ->formatStateUsing(fn ($state) => 'ZMW ' . number_format((float) $state, 2)),
                TextColumn::make('committed_amount')
                    ->label('Committed')
                    ->formatStateUsing(fn ($state) => 'ZMW ' . number_format((float) $state, 2)),
                TextColumn::make('spent_amount')
                    ->label('Spent')
                    ->formatStateUsing(fn ($state) => 'ZMW ' . number_format((float) $state, 2)),
                TextColumn::make('available')
                    ->label('Available')
                    ->getStateUsing(fn ($record) => 'ZMW ' . number_format($record->availableBalance(), 2))
                    ->color(fn ($record) => $record->availableBalance() < 0 ? 'danger' : 'success'),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->action(fn (BudgetAllocation $record) => $this->openEdit($record->id)),
            ])
            ->emptyStateHeading('No budget allocations found')
            ->emptyStateDescription('Add allocations for the selected fiscal year.');
    }

    public function updatedFiscalYear(): void
    {
        $this->resetTable();
    }

    public function openCreate(): void
    {
        $this->editingId      = null;
        $this->costCentreId   = 0;
        $this->modalFiscalYear = (string) $this->fiscalYear;
        $this->allocatedAmount = '';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $allocation = BudgetAllocation::findOrFail($id);

        $this->editingId       = $allocation->id;
        $this->costCentreId    = $allocation->cost_centre_id;
        $this->modalFiscalYear = (string) $allocation->fiscal_year;
        $this->allocatedAmount = (string) $allocation->allocated_amount;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate([
            'costCentreId'    => 'required|integer|exists:cost_centres,id',
            'modalFiscalYear' => 'required|integer|min:2020|max:2099',
            'allocatedAmount' => 'required|numeric|min:0',
        ]);

        // On create: check uniqueness of cost_centre_id + fiscal_year combo
        if (! $this->editingId) {
            $exists = BudgetAllocation::where('cost_centre_id', $this->costCentreId)
                ->where('fiscal_year', (int) $this->modalFiscalYear)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'costCentreId' => 'A budget allocation already exists for this cost centre and fiscal year.',
                ]);
            }

            BudgetAllocation::create([
                'cost_centre_id'   => $this->costCentreId,
                'fiscal_year'      => (int) $this->modalFiscalYear,
                'allocated_amount' => $this->allocatedAmount,
                'committed_amount' => 0,
                'spent_amount'     => 0,
            ]);

            $this->flash     = 'Budget allocation created successfully.';
            $this->flashType = 'success';
        } else {
            BudgetAllocation::findOrFail($this->editingId)->update([
                'cost_centre_id'   => $this->costCentreId,
                'fiscal_year'      => (int) $this->modalFiscalYear,
                'allocated_amount' => $this->allocatedAmount,
            ]);

            $this->flash     = 'Budget allocation updated successfully.';
            $this->flashType = 'success';
        }

        $this->closeModal();
        $this->resetTable();
    }

    public function dismissFlash(): void
    {
        $this->flash = null;
    }

    #[Computed]
    public function costCentres(): array
    {
        return CostCentre::with('department')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($cc) => [$cc->id => $cc->code . ' — ' . $cc->name])
            ->toArray();
    }

    #[Computed]
    public function editingAllocation(): ?BudgetAllocation
    {
        if (! $this->editingId) {
            return null;
        }

        return BudgetAllocation::find($this->editingId);
    }
};
