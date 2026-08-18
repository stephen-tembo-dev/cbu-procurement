<?php

namespace App\Http\Controllers;

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ReportExportController extends Controller
{
    private array $titles = [
        'approved_orders'  => 'Approved Orders',
        'paid_orders'      => 'Paid Orders',
        'pending_payments' => 'Pending Payments',
        'commitment'       => 'Budget Commitment',
        'undelivered'      => 'Undelivered Orders',
        'compliance'       => 'Compliance Exceptions',
    ];

    public function pdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'bursar', 'vc', 'procurement', 'auditor']),
            403
        );

        $type       = $request->get('type', 'approved_orders');
        $dateFrom   = $request->get('dateFrom') ?: null;
        $dateTo     = $request->get('dateTo') ?: null;
        $fiscalYear = (int) $request->get('fiscalYear', now()->year);

        ['headers' => $headers, 'rows' => $rows] = $this->buildData($type, $dateFrom, $dateTo, $fiscalYear);

        $title    = $this->titles[$type] ?? 'Report';
        $filename = Str::slug($title) . '-' . now()->format('Y-m-d') . '.pdf';

        return Pdf::view('reports.pdf.report', [
            'title'       => $title,
            'headers'     => $headers,
            'rows'        => $rows,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'fiscalYear'  => $fiscalYear,
            'generatedAt' => now()->format('d M Y, H:i'),
            'rowCount'    => count($rows),
        ])->name($filename)->download();
    }

    public function excel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'bursar', 'vc', 'procurement', 'auditor']),
            403
        );

        $type       = $request->get('type', 'approved_orders');
        $dateFrom   = $request->get('dateFrom') ?: null;
        $dateTo     = $request->get('dateTo') ?: null;
        $fiscalYear = (int) $request->get('fiscalYear', now()->year);

        ['headers' => $headers, 'rows' => $rows] = $this->buildData($type, $dateFrom, $dateTo, $fiscalYear);

        $title    = $this->titles[$type] ?? 'Report';
        $filename = Str::slug($title) . '-' . now()->format('Y-m-d') . '.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'report_') . '.xlsx';

        SimpleExcelWriter::create($tempPath)
            ->addHeader($headers)
            ->addRows($rows);

        return response()->download($tempPath, $filename)->deleteFileAfterSend();
    }

    // ── Shared data builder ────────────────────────────────────────────────────

    private function buildData(string $type, ?string $dateFrom, ?string $dateTo, int $fiscalYear): array
    {
        return match ($type) {
            'paid_orders'      => $this->paidOrdersData($dateFrom, $dateTo),
            'pending_payments' => $this->pendingPaymentsData($dateFrom, $dateTo),
            'commitment'       => $this->commitmentData($fiscalYear),
            'undelivered'      => $this->undeliveredData(),
            'compliance'       => $this->complianceData(),
            default            => $this->approvedOrdersData($dateFrom, $dateTo),
        };
    }

    private function approvedOrdersData(?string $dateFrom, ?string $dateTo): array
    {
        $rows = PurchaseOrder::with([
            'purchaseRequisition.requester',
            'purchaseRequisition.department',
            'supplier',
        ])
            ->where('status', 'issued')
            ->when($dateFrom, fn ($q) => $q->whereDate('issued_date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('issued_date', '<=', $dateTo))
            ->latest('issued_date')
            ->get()
            ->map(fn ($r) => [
                $r->po_number,
                $r->purchaseRequisition->reference_no ?? '—',
                $r->purchaseRequisition->department->name ?? '—',
                $r->supplier->name ?? '—',
                number_format((float) $r->total_value, 2),
                $r->issued_date?->format('d M Y') ?? '—',
            ])
            ->all();

        return [
            'headers' => ['PO Number', 'PR Reference', 'Department', 'Supplier', 'Value (ZMW)', 'Issued Date'],
            'rows'    => $rows,
        ];
    }

    private function paidOrdersData(?string $dateFrom, ?string $dateTo): array
    {
        $rows = Payment::with([
            'purchaseOrder.purchaseRequisition.department',
            'purchaseOrder.supplier',
            'bursarConfirmer',
        ])
            ->where('status', 'paid')
            ->when($dateFrom, fn ($q) => $q->whereDate('paid_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('paid_at', '<=', $dateTo))
            ->latest('paid_at')
            ->get()
            ->map(fn ($r) => [
                $r->purchaseOrder->po_number ?? '—',
                $r->purchaseOrder->purchaseRequisition->department->name ?? '—',
                $r->purchaseOrder->supplier->name ?? '—',
                number_format((float) $r->amount, 2),
                $r->bursarConfirmer->name ?? '—',
                $r->paid_at?->format('d M Y') ?? '—',
            ])
            ->all();

        return [
            'headers' => ['PO Number', 'Department', 'Supplier', 'Amount (ZMW)', 'Confirmed By', 'Payment Date'],
            'rows'    => $rows,
        ];
    }

    private function pendingPaymentsData(?string $dateFrom, ?string $dateTo): array
    {
        $rows = Payment::with([
            'purchaseOrder.purchaseRequisition.department',
            'purchaseOrder.supplier',
            'vcApprover',
        ])
            ->where('status', 'pending')
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->get()
            ->map(fn ($r) => [
                $r->purchaseOrder->po_number ?? '—',
                $r->purchaseOrder->purchaseRequisition->department->name ?? '—',
                $r->purchaseOrder->supplier->name ?? '—',
                number_format((float) $r->amount, 2),
                $r->vc_approved_at?->format('d M Y') ?? 'Awaiting VC',
                $r->delay_comment ?? '—',
            ])
            ->all();

        return [
            'headers' => ['PO Number', 'Department', 'Supplier', 'Amount (ZMW)', 'VC Approved', 'Delay Note'],
            'rows'    => $rows,
        ];
    }

    private function commitmentData(int $fiscalYear): array
    {
        $rows = BudgetAllocation::with(['costCentre.department'])
            ->where('fiscal_year', $fiscalYear ?: now()->year)
            ->orderBy('cost_centre_id')
            ->get()
            ->map(fn ($r) => [
                $r->costCentre->department->name ?? '—',
                $r->costCentre->name ?? '—',
                $r->costCentre->code ?? '—',
                number_format((float) $r->allocated_amount, 2),
                number_format((float) $r->committed_amount, 2),
                number_format((float) $r->spent_amount, 2),
                number_format($r->availableBalance(), 2),
            ])
            ->all();

        return [
            'headers' => ['Department', 'Cost Centre', 'Code', 'Allocated (ZMW)', 'Committed (ZMW)', 'Spent (ZMW)', 'Available (ZMW)'],
            'rows'    => $rows,
        ];
    }

    private function undeliveredData(): array
    {
        $rows = PurchaseOrder::with(['purchaseRequisition.department', 'supplier'])
            ->whereIn('status', ['issued', 'partially_delivered'])
            ->orderBy('issued_date')
            ->get()
            ->map(fn ($r) => [
                $r->po_number,
                $r->purchaseRequisition->department->name ?? '—',
                $r->supplier->name ?? '—',
                number_format((float) $r->total_value, 2),
                ucwords(str_replace('_', ' ', $r->status)),
                $r->issued_date?->format('d M Y') ?? '—',
                $r->expected_delivery_date?->format('d M Y') ?? '—',
            ])
            ->all();

        return [
            'headers' => ['PO Number', 'Department', 'Supplier', 'Value (ZMW)', 'Status', 'Issued Date', 'Expected Delivery'],
            'rows'    => $rows,
        ];
    }

    private function complianceData(): array
    {
        $rows = PurchaseRequisition::with(['requester', 'department', 'items'])
            ->whereIn('status', ['pending_procurement', 'pending_audit'])
            ->latest()
            ->get()
            ->map(fn ($r) => [
                $r->reference_no,
                $r->requester->name ?? '—',
                $r->department->name ?? '—',
                number_format($r->totalEstimated(), 2),
                ucwords(str_replace('_', ' ', $r->status)),
                $r->created_at->format('d M Y'),
            ])
            ->all();

        return [
            'headers' => ['Reference', 'Requester', 'Department', 'Est. Value (ZMW)', 'Status', 'Submitted'],
            'rows'    => $rows,
        ];
    }
}
