<?php

use App\Models\Procurement\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
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

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['procurement', 'stores', 'auditor', 'admin', 'vc', 'bursar']),
            403
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::with(['supplier', 'purchaseRequisition.department'])
                    ->latest()
            )
            ->recordUrl(fn (PurchaseOrder $record): string => route('po.show', $record))
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->weight('semibold')
                    ->url(fn ($record) => route('po.show', $record->id))
                    ->color('primary'),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
                TextColumn::make('purchaseRequisition.reference_no')
                    ->label('PR Reference')
                    ->url(fn ($record) => route('requisitions.show', $record->purchase_requisition_id))
                    ->color('primary'),
                TextColumn::make('purchaseRequisition.department.name')
                    ->label('Department'),
                TextColumn::make('total_value')
                    ->label('Total Value')
                    ->formatStateUsing(fn ($state) => 'ZMW ' . number_format((float) $state, 2)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered'           => 'success',
                        'issued'              => 'warning',
                        'partially_delivered' => 'info',
                        'draft'               => 'gray',
                        default               => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Str::title(str_replace('_', ' ', $state))),
                TextColumn::make('issued_date')
                    ->label('Issued')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('expected_delivery_date')
                    ->label('Expected Delivery')
                    ->date('d M Y')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft'               => 'Draft',
                        'issued'              => 'Issued',
                        'partially_delivered' => 'Partially Delivered',
                        'delivered'           => 'Delivered',
                    ]),
            ], layout: FiltersLayout::Modal)
            ->filtersTriggerAction(fn (Action $action) => $action->button()->label('Filters'))
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('po.show', $record->id)),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No purchase orders yet')
            ->emptyStateDescription('Purchase orders will appear here once the procurement officer creates them.');
    }
};
