<?php

use App\Models\Procurement\PurchaseRequisition;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    #[Computed]
    public function stats(): array
    {
        $user  = auth()->user();
        $query = PurchaseRequisition::query();

        if ($user->hasRole('hod') && ! $user->hasRole('admin')) {
            $query->where('department_id', $user->department_id);
        } elseif (! $user->hasAnyRole(['hod', 'stores', 'bursar', 'vc', 'procurement', 'auditor', 'admin'])) {
            $query->where('requester_id', $user->id);
        }

        return [
            'total'     => (clone $query)->count(),
            'active'    => (clone $query)->whereNotIn('status', ['paid', 'delivered', 'rejected', 'suspended', 'draft'])->count(),
            'completed' => (clone $query)->whereIn('status', ['paid', 'delivered'])->count(),
            'rejected'  => (clone $query)->whereIn('status', ['rejected', 'suspended'])->count(),
        ];
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        $roleStatusMap = [
            'hod'         => ['pending_hod'],
            'stores'      => ['pending_stores'],
            'bursar'      => ['pending_bursar', 'pending_payment'],
            'vc'          => ['pending_vc_requisition', 'pending_vc_payment'],
            'procurement' => ['pending_procurement'],
            'auditor'     => ['pending_audit'],
        ];

        $inboxStatuses = [];
        foreach ($roleStatusMap as $role => $statuses) {
            if ($user->hasRole($role)) {
                $inboxStatuses = array_merge($inboxStatuses, $statuses);
            }
        }

        $query = PurchaseRequisition::query()
            ->with(['requester', 'department', 'costCentre']);

        if (! empty($inboxStatuses)) {
            $query->whereIn('status', array_unique($inboxStatuses));

            if ($user->hasRole('hod') && ! $user->hasRole('admin')) {
                $query->whereHas('department', fn ($q) => $q->where('hod_user_id', $user->id));
            }
        } else {
            $query->where('requester_id', $user->id)
                ->whereNotIn('status', ['draft', 'paid', 'delivered', 'rejected', 'suspended']);
        }

        return $table
            ->query($query->latest())
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->url(fn ($record) => route('requisitions.show', $record->id))
                    ->color('primary')
                    ->weight('semibold'),
                TextColumn::make('requester.name')
                    ->label('Requester'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'product' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state): string => Str::ucfirst($state)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid', 'delivered', 'issued_from_stores' => 'success',
                        'rejected', 'suspended'                   => 'danger',
                        'draft'                                   => 'gray',
                        default                                   => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', Str::title($state))),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since(),
            ])
            ->emptyStateHeading('Your inbox is clear')
            ->emptyStateDescription('No purchase requisitions are waiting for your action.');
    }
};
