<?php

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisition;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable;

    public string $activeReport = 'approved_orders';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public int    $fiscalYear;

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'bursar', 'vc', 'procurement', 'auditor']),
            403
        );
        $this->fiscalYear = now()->year;
    }

    public function switchReport(string $report): void
    {
        $this->activeReport = $report;
        $this->dateFrom     = '';
        $this->dateTo       = '';
        $this->resetTable();
    }

    public function applyDates(): void
    {
        // Triggers re-render; date properties already updated via wire:model
    }

    public function exportPdf(): void
    {
        $this->dispatch('open-export-url', url: route('reports.export.pdf', array_filter([
            'type'       => $this->activeReport,
            'dateFrom'   => $this->dateFrom ?: null,
            'dateTo'     => $this->dateTo ?: null,
            'fiscalYear' => $this->fiscalYear,
        ])));
    }

    public function exportExcel(): void
    {
        $this->dispatch('open-export-url', url: route('reports.export.excel', array_filter([
            'type'       => $this->activeReport,
            'dateFrom'   => $this->dateFrom ?: null,
            'dateTo'     => $this->dateTo ?: null,
            'fiscalYear' => $this->fiscalYear,
        ])));
    }

    public function table(Table $table): Table
    {
        return match ($this->activeReport) {
            'paid_orders'      => $this->paidOrdersTable($table),
            'pending_payments' => $this->pendingPaymentsTable($table),
            'commitment'       => $this->commitmentTable($table),
            'undelivered'      => $this->undeliveredTable($table),
            'compliance'       => $this->complianceTable($table),
            default            => $this->approvedOrdersTable($table),
        };
    }

    // ── Report table definitions ─────────────────────────────────────────────

    private function approvedOrdersTable(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::with(['purchaseRequisition.requester', 'purchaseRequisition.department', 'supplier'])
                    ->where('status', 'issued')
                    ->when($this->dateFrom, fn ($q) => $q->whereDate('issued_date', '>=', $this->dateFrom))
                    ->when($this->dateTo,   fn ($q) => $q->whereDate('issued_date', '<=', $this->dateTo))
                    ->latest('issued_date')
            )
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO Number')
                    ->weight('semibold')
                    ->url(fn ($r) => $r ? route('po.show', $r->id) : null)
                    ->color('primary'),
                TextColumn::make('purchaseRequisition.reference_no')
                    ->label('PR Reference')
                    ->url(fn ($r) => $r ? route('requisitions.show', $r->purchase_requisition_id) : null)
                    ->color('primary'),
                TextColumn::make('purchaseRequisition.department.name')
                    ->label('Department'),
                TextColumn::make('supplier.name')
                    ->label('Supplier'),
                TextColumn::make('total_value')
                    ->label('Value (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('issued_date')
                    ->label('Issued')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('issued_date', 'desc')
            ->emptyStateHeading('No approved orders found')
            ->emptyStateDescription('Issued purchase orders will appear here.');
    }

    private function paidOrdersTable(Table $table): Table
    {
        return $table
            ->query(
                Payment::with(['purchaseOrder.purchaseRequisition.department', 'purchaseOrder.supplier', 'bursarConfirmer'])
                    ->where('status', 'paid')
                    ->when($this->dateFrom, fn ($q) => $q->whereDate('paid_at', '>=', $this->dateFrom))
                    ->when($this->dateTo,   fn ($q) => $q->whereDate('paid_at', '<=', $this->dateTo))
                    ->latest('paid_at')
            )
            ->columns([
                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->url(fn ($r) => $r ? route('po.show', $r->purchase_order_id) : null)
                    ->color('primary'),
                TextColumn::make('purchaseOrder.purchaseRequisition.department.name')
                    ->label('Department'),
                TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier'),
                TextColumn::make('amount')
                    ->label('Amount (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('bursarConfirmer.name')
                    ->label('Confirmed By')
                    ->placeholder('—'),
                TextColumn::make('paid_at')
                    ->label('Payment Date')
                    ->date('d M Y')
                    ->placeholder('—'),
            ])
            ->defaultSort('paid_at', 'desc')
            ->emptyStateHeading('No paid orders found');
    }

    private function pendingPaymentsTable(Table $table): Table
    {
        return $table
            ->query(
                Payment::with(['purchaseOrder.purchaseRequisition.department', 'purchaseOrder.supplier', 'vcApprover'])
                    ->where('status', 'pending')
                    ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                    ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                    ->latest()
            )
            ->columns([
                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->url(fn ($r) => $r ? route('po.show', $r->purchase_order_id) : null)
                    ->color('primary'),
                TextColumn::make('purchaseOrder.purchaseRequisition.department.name')
                    ->label('Department'),
                TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier'),
                TextColumn::make('amount')
                    ->label('Amount (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('vc_approved_at')
                    ->label('VC Approved')
                    ->date('d M Y')
                    ->placeholder('Awaiting VC'),
                TextColumn::make('delay_comment')
                    ->label('Delay Note')
                    ->placeholder('—')
                    ->limit(40),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No pending payments');
    }

    private function commitmentTable(Table $table): Table
    {
        return $table
            ->query(
                BudgetAllocation::with(['costCentre.department'])
                    ->where('fiscal_year', $this->fiscalYear)
                    ->orderBy('cost_centre_id')
            )
            ->columns([
                TextColumn::make('costCentre.department.name')
                    ->label('Department'),
                TextColumn::make('costCentre.name')
                    ->label('Cost Centre'),
                TextColumn::make('costCentre.code')
                    ->label('Code'),
                TextColumn::make('allocated_amount')
                    ->label('Allocated (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('committed_amount')
                    ->label('Committed (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('spent_amount')
                    ->label('Spent (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('available')
                    ->label('Available (ZMW)')
                    ->state(fn ($record) => $record->availableBalance())
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2))
                    ->color(fn ($state) => (float) $state < 0 ? 'danger' : 'success'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No budget allocations for ' . $this->fiscalYear);
    }

    private function undeliveredTable(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::with(['purchaseRequisition.department', 'supplier'])
                    ->whereIn('status', ['issued', 'partially_delivered'])
                    ->orderBy('issued_date')
            )
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO Number')
                    ->url(fn ($r) => $r ? route('po.show', $r->id) : null)
                    ->color('primary'),
                TextColumn::make('purchaseRequisition.department.name')
                    ->label('Department'),
                TextColumn::make('supplier.name')
                    ->label('Supplier'),
                TextColumn::make('total_value')
                    ->label('Value (ZMW)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state === 'issued' ? 'warning' : 'info')
                    ->formatStateUsing(fn ($state) => Str::title(str_replace('_', ' ', $state))),
                TextColumn::make('issued_date')
                    ->label('Issued')
                    ->date('d M Y'),
                TextColumn::make('expected_delivery_date')
                    ->label('Expected Delivery')
                    ->date('d M Y')
                    ->placeholder('—'),
            ])
            ->defaultSort('issued_date', 'asc')
            ->emptyStateHeading('No undelivered orders')
            ->emptyStateDescription('All issued purchase orders have been delivered.');
    }

    private function complianceTable(Table $table): Table
    {
        return $table
            ->query(
                PurchaseRequisition::with(['requester', 'department', 'items'])
                    ->whereIn('status', ['pending_procurement', 'pending_audit'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->weight('semibold')
                    ->url(fn ($r) => $r ? route('requisitions.show', $r->id) : null)
                    ->color('primary'),
                TextColumn::make('requester.name')
                    ->label('Requester'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('estimated_value')
                    ->label('Est. Value (ZMW)')
                    ->state(fn ($record) => $record->totalEstimated())
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state === 'pending_audit' ? 'danger' : 'warning')
                    ->formatStateUsing(fn ($state) => Str::title(str_replace('_', ' ', $state))),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date('d M Y'),
            ])
            ->emptyStateHeading('No compliance exceptions')
            ->emptyStateDescription('All requisitions have cleared procurement and audit stages.');
    }
};
