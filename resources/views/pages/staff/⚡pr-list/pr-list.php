<?php

use App\Models\Procurement\PurchaseRequisition;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = auth()->user();

        $query = PurchaseRequisition::query()
            ->with(['requester', 'department', 'costCentre']);

        if ($user->hasRole('admin')) {
            // Admin sees all
        } elseif ($user->hasAnyRole(['stores', 'bursar', 'vc', 'procurement', 'auditor'])) {
            // These roles see all PRs that have passed their stage
        } elseif ($user->hasRole('hod')) {
            $query->where('department_id', $user->department_id);
        } else {
            $query->where('requester_id', $user->id);
        }

        return $table
            ->query($query->latest())
            ->recordUrl(fn (PurchaseRequisition $record): string => route('requisitions.show', $record))
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->url(fn ($record) => route('requisitions.show', $record->id))
                    ->color('primary')
                    ->weight('semibold'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'product' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state): string => Str::ucfirst($state)),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable(),
                TextColumn::make('costCentre.name')
                    ->label('Cost Centre'),
                TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable(),
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
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'                  => 'Draft',
                        'pending_hod'            => 'Pending HOD',
                        'pending_stores'         => 'Pending Stores',
                        'issued_from_stores'     => 'Issued from Stores',
                        'pending_bursar'         => 'Pending Bursar',
                        'pending_vc_requisition' => 'Pending VC Requisition',
                        'pending_procurement'    => 'Pending Procurement',
                        'pending_audit'          => 'Pending Audit',
                        'pending_vc_payment'     => 'Pending VC Payment',
                        'pending_payment'        => 'Pending Payment',
                        'paid'                   => 'Paid',
                        'delivered'              => 'Delivered',
                        'rejected'               => 'Rejected',
                        'suspended'              => 'Suspended',
                    ]),
                SelectFilter::make('type')
                    ->options(['product' => 'Product', 'service' => 'Service']),
            ], layout: FiltersLayout::Modal)
            ->filtersTriggerAction(fn (Action $action) => $action->button()->label('Filters'))
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('requisitions.show', $record->id)),
            ])
            ->emptyStateHeading('No requisitions found')
            ->emptyStateDescription('There are no purchase requisitions matching your filters.');
    }
};
