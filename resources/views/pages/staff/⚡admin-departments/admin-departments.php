<?php

use App\Models\CostCentre;
use App\Models\Department;
use App\Models\User;
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
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public string $activeSection = 'departments';

    // Department modal state
    public bool    $showDeptModal  = false;
    public ?int    $editingDeptId  = null;
    public string  $deptName       = '';
    public string  $deptCode       = '';
    public int     $deptHodId      = 0;
    public bool    $deptIsActive   = true;

    // Cost centre modal state
    public bool    $showCcModal     = false;
    public ?int    $managingDeptId  = null;
    public string  $ccCode          = '';
    public string  $ccName          = '';
    public bool    $ccIsActive      = true;

    // Flash
    public ?string $flash     = null;
    public ?string $flashType = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Department::with(['hod', 'costCentres'])->withCount('costCentres')->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('hod.name')
                    ->label('HOD')
                    ->default('—'),
                TextColumn::make('cost_centres_count')
                    ->label('Cost Centres')
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->action(fn ($record) => $this->openEditDept($record->id)),
                Action::make('costCentres')
                    ->label('Cost Centres')
                    ->icon('heroicon-o-building-office')
                    ->action(fn ($record) => $this->openCostCentres($record->id)),
                Action::make('toggleActive')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn ($record) => $this->toggleDeptActive($record->id)),
            ])
            ->emptyStateHeading('No departments found')
            ->emptyStateDescription('Add your first department to get started.');
    }

    public function openCreateDept(): void
    {
        $this->editingDeptId = null;
        $this->deptName      = '';
        $this->deptCode      = '';
        $this->deptHodId     = 0;
        $this->deptIsActive  = true;
        $this->resetErrorBag();
        $this->showDeptModal = true;
    }

    public function openEditDept(int $id): void
    {
        $dept                = Department::findOrFail($id);
        $this->editingDeptId = $dept->id;
        $this->deptName      = $dept->name;
        $this->deptCode      = $dept->code;
        $this->deptHodId     = $dept->hod_user_id ?? 0;
        $this->deptIsActive  = (bool) $dept->is_active;
        $this->resetErrorBag();
        $this->showDeptModal = true;
    }

    public function closeDeptModal(): void
    {
        $this->showDeptModal = false;
        $this->resetErrorBag();
    }

    public function saveDept(): void
    {
        $uniqueRule = 'required|string|max:20|unique:departments,code';
        if ($this->editingDeptId) {
            $uniqueRule .= ',' . $this->editingDeptId;
        }

        $this->validate([
            'deptName'     => ['required', 'string', 'max:255'],
            'deptCode'     => [$uniqueRule],
            'deptHodId'    => ['nullable', 'integer'],
            'deptIsActive' => ['boolean'],
        ]);

        $data = [
            'name'        => $this->deptName,
            'code'        => strtoupper($this->deptCode),
            'hod_user_id' => $this->deptHodId ?: null,
            'is_active'   => $this->deptIsActive,
        ];

        if ($this->editingDeptId) {
            Department::findOrFail($this->editingDeptId)->update($data);
            $this->flash = 'Department updated successfully.';
        } else {
            Department::create($data);
            $this->flash = 'Department created successfully.';
        }

        $this->flashType = 'success';
        $this->closeDeptModal();
        $this->resetTable();
    }

    public function toggleDeptActive(int $id): void
    {
        $dept            = Department::findOrFail($id);
        $dept->is_active = ! $dept->is_active;
        $dept->save();

        $this->flash     = $dept->is_active ? 'Department activated.' : 'Department deactivated.';
        $this->flashType = 'success';
        $this->resetTable();
    }

    public function openCostCentres(int $deptId): void
    {
        $this->managingDeptId = $deptId;
        $this->ccCode         = '';
        $this->ccName         = '';
        $this->ccIsActive     = true;
        $this->resetErrorBag();
        $this->showCcModal    = true;
    }

    public function closeCcModal(): void
    {
        $this->showCcModal    = false;
        $this->managingDeptId = null;
    }

    public function saveCostCentre(): void
    {
        $this->validate([
            'ccCode' => ['required', 'string', 'max:20', 'unique:cost_centres,code'],
            'ccName' => ['required', 'string', 'max:255'],
        ]);

        CostCentre::create([
            'code'          => strtoupper($this->ccCode),
            'name'          => $this->ccName,
            'department_id' => $this->managingDeptId,
            'is_active'     => $this->ccIsActive,
        ]);

        $this->ccCode     = '';
        $this->ccName     = '';
        $this->ccIsActive = true;
        $this->resetErrorBag();

        $this->flash     = 'Cost centre added successfully.';
        $this->flashType = 'success';
        $this->resetTable();
    }

    public function toggleCostCentreActive(int $id): void
    {
        $cc            = CostCentre::findOrFail($id);
        $cc->is_active = ! $cc->is_active;
        $cc->save();

        $this->flash     = $cc->is_active ? 'Cost centre activated.' : 'Cost centre deactivated.';
        $this->flashType = 'success';
    }

    public function dismissFlash(): void
    {
        $this->flash     = null;
        $this->flashType = null;
    }

    #[Computed]
    public function usersForHod(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function costCentresForDept()
    {
        return $this->managingDeptId
            ? CostCentre::where('department_id', $this->managingDeptId)->latest()->get()
            : collect();
    }

    #[Computed]
    public function managingDept(): ?Department
    {
        return $this->managingDeptId ? Department::find($this->managingDeptId) : null;
    }
};
