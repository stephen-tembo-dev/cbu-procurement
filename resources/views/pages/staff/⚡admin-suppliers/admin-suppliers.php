<?php

use App\Models\Supplier;
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
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public string $activeSection = 'suppliers';

    // Modal state
    public bool $showModal    = false;
    public ?int  $editingId   = null;

    // Form fields
    public string $name              = '';
    public string $contactPerson     = '';
    public string $contactEmail      = '';
    public string $contactPhone      = '';
    public string $zppaRegistration  = '';
    public bool   $zppaApproved      = false;
    public bool   $isActive          = true;

    // Flash
    public ?string $flash     = null;
    public string  $flashType = 'success';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('suppliers.manage'), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Supplier::latest())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('contact_person')
                    ->label('Contact')
                    ->default('—'),
                TextColumn::make('contact_email')
                    ->label('Email')
                    ->default('—'),
                TextColumn::make('zppa_registration')
                    ->label('ZPPA Reg')
                    ->default('—'),
                IconColumn::make('zppa_approved')
                    ->label('ZPPA Approved')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->action(fn (Supplier $record) => $this->openEdit($record->id)),
                Action::make('toggleActive')
                    ->label(fn (Supplier $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Supplier $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Supplier $record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn (Supplier $record) => $this->toggleActive($record->id)),
            ])
            ->emptyStateHeading('No suppliers found')
            ->emptyStateDescription('Add your first supplier to get started.');
    }

    public function openCreate(): void
    {
        $this->editingId        = null;
        $this->name             = '';
        $this->contactPerson    = '';
        $this->contactEmail     = '';
        $this->contactPhone     = '';
        $this->zppaRegistration = '';
        $this->zppaApproved     = false;
        $this->isActive         = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $supplier = Supplier::findOrFail($id);

        $this->editingId        = $supplier->id;
        $this->name             = $supplier->name;
        $this->contactPerson    = $supplier->contact_person ?? '';
        $this->contactEmail     = $supplier->contact_email ?? '';
        $this->contactPhone     = $supplier->contact_phone ?? '';
        $this->zppaRegistration = $supplier->zppa_registration ?? '';
        $this->zppaApproved     = (bool) $supplier->zppa_approved;
        $this->isActive         = (bool) $supplier->is_active;
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
            'name'             => 'required|string|max:255',
            'contactEmail'     => 'nullable|email|max:255',
            'zppaApproved'     => 'boolean',
            'isActive'         => 'boolean',
        ]);

        $data = [
            'name'              => $this->name,
            'contact_person'    => $this->contactPerson ?: null,
            'contact_email'     => $this->contactEmail  ?: null,
            'contact_phone'     => $this->contactPhone  ?: null,
            'zppa_registration' => $this->zppaRegistration ?: null,
            'zppa_approved'     => $this->zppaApproved,
            'is_active'         => $this->isActive,
        ];

        if ($this->editingId) {
            Supplier::findOrFail($this->editingId)->update($data);
            $this->flash     = 'Supplier updated successfully.';
            $this->flashType = 'success';
        } else {
            Supplier::create($data);
            $this->flash     = 'Supplier created successfully.';
            $this->flashType = 'success';
        }

        $this->closeModal();
        $this->resetTable();
    }

    public function toggleActive(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update(['is_active' => ! $supplier->is_active]);

        $this->flash     = 'Supplier ' . ($supplier->is_active ? 'activated' : 'deactivated') . '.';
        $this->flashType = 'success';
        $this->resetTable();
    }

    public function dismissFlash(): void
    {
        $this->flash = null;
    }
};
