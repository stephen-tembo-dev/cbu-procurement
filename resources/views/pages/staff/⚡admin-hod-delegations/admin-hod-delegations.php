<?php

use App\Models\Department;
use App\Models\HodDelegation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public bool    $showModal      = false;
    public ?int    $delegateeId    = null;
    public ?int    $departmentId   = null;
    public string  $startsAt       = '';
    public string  $endsAt         = '';
    public string  $reason         = '';

    public ?string $flash     = null;
    public ?string $flashType = null;

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'hod']),
            403
        );

        // Pre-select the HOD's own department when accessed as HOD
        if (auth()->user()->hasRole('hod') && ! auth()->user()->hasRole('admin')) {
            $dept = Department::where('hod_user_id', auth()->id())->first();
            $this->departmentId = $dept?->id;
        }
    }

    public function table(Table $table): Table
    {
        $query = HodDelegation::with(['department', 'hod', 'delegate', 'revokedBy'])
            ->latest();

        // HODs only see their own department's delegations
        if (auth()->user()->hasRole('hod') && ! auth()->user()->hasRole('admin')) {
            $query->where('hod_user_id', auth()->id());
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('department.name')
                    ->label('Department')
                    ->weight('semibold'),
                TextColumn::make('hod.name')
                    ->label('HOD')
                    ->placeholder('—'),
                TextColumn::make('delegate.name')
                    ->label('Delegate'),
                TextColumn::make('starts_at')
                    ->label('From')
                    ->date('d M Y'),
                TextColumn::make('ends_at')
                    ->label('To')
                    ->date('d M Y'),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn ($record) => $record->statusLabel())
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Active'     => 'success',
                        'Scheduled'  => 'info',
                        'Expired'    => 'gray',
                        'Revoked'    => 'danger',
                        default      => 'gray',
                    }),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->placeholder('—')
                    ->limit(40),
                TextColumn::make('revoked_at')
                    ->label('Revoked')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_active && $record->ends_at->isFuture())
                    ->requiresConfirmation()
                    ->modalHeading('Revoke Delegation')
                    ->modalDescription('This will immediately end the delegation. The delegate will no longer be able to approve requisitions for this department.')
                    ->action(fn ($record) => $this->revokeDelegation($record->id)),
            ])
            ->emptyStateHeading('No delegations found')
            ->emptyStateDescription('Create a delegation to allow a colleague to approve requisitions on your behalf while you are away.');
    }

    public function openCreate(): void
    {
        $this->delegateeId  = null;
        $this->startsAt     = now()->toDateString();
        $this->endsAt       = '';
        $this->reason       = '';
        $this->resetErrorBag();
        $this->showModal    = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    public function saveDelegation(): void
    {
        $this->validate([
            'departmentId' => ['required', 'integer', 'exists:departments,id'],
            'delegateeId'  => ['required', 'integer', 'exists:users,id'],
            'startsAt'     => ['required', 'date'],
            'endsAt'       => ['required', 'date', 'after:startsAt'],
            'reason'       => ['nullable', 'string', 'max:500'],
        ]);

        $dept = Department::findOrFail($this->departmentId);

        // HODs may only delegate their own department
        if (auth()->user()->hasRole('hod') && ! auth()->user()->hasRole('admin')) {
            abort_unless($dept->hod_user_id === auth()->id(), 403);
        }

        // Cannot delegate to yourself
        if ($this->delegateeId === $dept->hod_user_id) {
            $this->addError('delegateeId', 'You cannot delegate to yourself.');
            return;
        }

        // Revoke any existing active delegation for this department before creating a new one
        HodDelegation::where('department_id', $this->departmentId)
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->get()
            ->each->revoke(auth()->id());

        HodDelegation::create([
            'department_id'    => $this->departmentId,
            'hod_user_id'      => $dept->hod_user_id,
            'delegate_user_id' => $this->delegateeId,
            'reason'           => $this->reason ?: null,
            'starts_at'        => $this->startsAt,
            'ends_at'          => $this->endsAt,
            'is_active'        => true,
        ]);

        $this->flash     = 'Delegation created successfully.';
        $this->flashType = 'success';
        $this->closeModal();
        $this->resetTable();
    }

    public function revokeDelegation(int $id): void
    {
        $delegation = HodDelegation::findOrFail($id);

        // HODs may only revoke their own department's delegation
        if (auth()->user()->hasRole('hod') && ! auth()->user()->hasRole('admin')) {
            abort_unless($delegation->hod_user_id === auth()->id(), 403);
        }

        $delegation->revoke(auth()->id());

        $this->flash     = 'Delegation revoked.';
        $this->flashType = 'success';
        $this->resetTable();
    }

    public function dismissFlash(): void
    {
        $this->flash     = null;
        $this->flashType = null;
    }

    #[Computed]
    public function departments(): array
    {
        if (auth()->user()->hasRole('hod') && ! auth()->user()->hasRole('admin')) {
            return Department::where('hod_user_id', auth()->id())
                ->where('is_active', true)
                ->pluck('name', 'id')
                ->toArray();
        }

        return Department::where('is_active', true)->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function users(): array
    {
        return User::orderBy('name')->pluck('name', 'id')->toArray();
    }
};
