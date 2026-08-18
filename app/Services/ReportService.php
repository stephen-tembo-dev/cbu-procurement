<?php

namespace App\Services;

use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    public function __construct(
        private readonly PurchaseRequisitionRepositoryInterface $prRepo,
        private readonly PurchaseOrderRepositoryInterface       $poRepo,
        private readonly PaymentRepositoryInterface             $paymentRepo,
        private readonly BudgetAllocationRepositoryInterface    $budgetRepo,
        private readonly SupplierQuoteRepositoryInterface       $quoteRepo,
    ) {}

    /** Commitment report: all budget allocations for a fiscal year. */
    public function commitmentReport(int $fiscalYear): Collection
    {
        return $this->budgetRepo->findAllForYear($fiscalYear);
    }

    /** All approved (VC-approved) POs — the 'Approved Orders' report. */
    public function approvedOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->poRepo->findForReport('issued', $dateFrom, $dateTo, $perPage);
    }

    /** All POs whose associated payment is marked 'paid'. */
    public function paidOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paymentRepo->findForReport('paid', $dateFrom, $dateTo, $perPage);
    }

    /** POs with a payment in 'pending' status. */
    public function pendingOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paymentRepo->findForReport('pending', $dateFrom, $dateTo, $perPage);
    }

    /** POs where the service has been received (status = delivered, type = service). */
    public function servicedOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->poRepo->findForReport('delivered', $dateFrom, $dateTo, $perPage);
    }

    /** POs issued but not yet confirmed as delivered. */
    public function undeliveredOrdersReport(): Collection
    {
        return $this->poRepo->findUndelivered();
    }

    /** All PRs with outstanding compliance flags. */
    public function complianceExceptionsReport(int $perPage = 20): LengthAwarePaginator
    {
        return $this->prRepo->findByStatus(
            ['pending_procurement', 'pending_audit'],
            $perPage
        );
    }

    /** Dashboard counts for the VC and admin summary panel. */
    public function dashboardCounts(): array
    {
        return $this->prRepo->countByStatus();
    }
}
