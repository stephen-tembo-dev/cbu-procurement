<?php

namespace App\Contracts;

use App\Models\Finance\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPurchaseOrder(int $poId): ?Payment;

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator;

    /** Payments awaiting VC approval. */
    public function findPendingVcApproval(): Collection;

    /** Payments VC-approved but awaiting Bursar fund confirmation. */
    public function findPendingBursarConfirmation(): Collection;

    /** Payments with a delay comment (overdue). */
    public function findDelayed(): Collection;

    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator;
}
