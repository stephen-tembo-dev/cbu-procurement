<?php

namespace App\Contracts;

use App\Models\Procurement\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPoNumber(string $poNumber): ?PurchaseOrder;

    public function findByRequisition(int $prId): ?PurchaseOrder;

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator;

    /** POs that have been issued but not yet confirmed as delivered. */
    public function findUndelivered(): Collection;

    /** POs for the reports screen with date range filtering. */
    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator;

    public function updateStatus(int $id, string $status): bool;
}
