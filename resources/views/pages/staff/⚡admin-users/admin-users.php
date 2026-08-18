<?php

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

    public bool $showModal    = false;
    public ?int $editingId    = null;
    public string $name       = '';
    public string $email      = '';
    public string $password   = '';
    public int $departmentId  = 0;
    public string $role       = 'requester';
    public bool $isActive     = true;
    public ?string $flash     = null;
    public ?string $flashType = null;

    public string $activeSection = 'users';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::with(['department', 'roles'])->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('roles.0.name')
                    ->label('Role')
                    ->badge()
                    ->color('info'),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->default('—'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->action(fn ($record) => $this->openEdit($record->id)),
                Action::make('toggleActive')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn ($record) => $this->toggleActive($record->id)),
            ]);
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'departmentId', 'role', 'isActive']);
        $this->role       = 'requester';
        $this->isActive   = true;
        $this->departmentId = 0;
        $this->editingId  = null;
        $this->resetErrorBag();
        $this->showModal  = true;
    }

    public function openEdit(int $id): void
    {
        $user            = User::with('roles')->findOrFail($id);
        $this->editingId = $user->id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->password  = '';
        $this->departmentId = $user->department_id ?? 0;
        $this->role      = $user->roles->first()?->name ?? 'requester';
        $this->isActive  = (bool) $user->is_active;
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
        $rules = [
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'unique:users,email' . ($this->editingId ? ',' . $this->editingId : '')],
            'role'         => ['required', 'in:requester,hod,stores,bursar,vc,procurement,auditor,admin'],
            'isActive'     => ['boolean'],
            'departmentId' => ['nullable', 'integer'],
        ];

        if ($this->editingId === null) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update([
                'name'          => $this->name,
                'email'         => $this->email,
                'department_id' => $this->departmentId ?: null,
                'is_active'     => $this->isActive,
            ]);
        } else {
            $user = User::create([
                'name'          => $this->name,
                'email'         => $this->email,
                'password'      => bcrypt($this->password),
                'department_id' => $this->departmentId ?: null,
                'is_active'     => $this->isActive,
            ]);
        }

        $user->syncRoles([$this->role]);

        $this->closeModal();
        $this->flash     = $this->editingId ? 'User updated successfully.' : 'User created successfully.';
        $this->flashType = 'success';
    }

    public function toggleActive(int $id): void
    {
        $user            = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        $this->flash     = $user->is_active ? 'User activated.' : 'User deactivated.';
        $this->flashType = 'success';
    }

    public function dismissFlash(): void
    {
        $this->flash     = null;
        $this->flashType = null;
    }

    #[Computed]
    public function departments(): array
    {
        return Department::orderBy('name')->pluck('name', 'id')->toArray();
    }
};
