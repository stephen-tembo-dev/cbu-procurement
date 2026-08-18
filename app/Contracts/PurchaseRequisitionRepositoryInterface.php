<?php

namespace App\Contracts;

use App\Models\Procurement\PurchaseRequisition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRequisitionRepositoryInterface extends BaseRepositoryInterface
{
    public function findByReference(string $reference): ?PurchaseRequisition;

    /**
     * Full PR with all relations needed to render the audit trail summary:
     * requester, department, costCentre, items.stockItem,
     * approvalRecords.actor, supplierQuotes.supplier,
     * purchaseOrder.supplier, purchaseOrder.payment,
     * attachments.uploader.
     */
    public function findWithFullTrail(int $id): ?PurchaseRequisition;

    /** PRs raised by a specific user. */
    public function findByRequester(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** PRs belonging to a department. */
    public function findByDepartment(int $departmentId, int $perPage = 20): LengthAwarePaginator;

    /**
     * PRs sitting at a specific workflow stage, ready for action.
     * $status can be a single string or an array of statuses.
     */
    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator;

    /**
     * Dashboard inbox: PRs the given user needs to act on
     * based on their role and the current PR status.
     */
    public function findPendingForUser(int $userId, string $role): LengthAwarePaginator;

    /** Atomically update the status column. */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Count PRs grouped by status.
     * Returns [ 'pending_hod' => 3, 'pending_stores' => 1, ... ]
     */
    public function countByStatus(): array;

    /**
     * PRs with a service_consumed_flag that have not yet been reviewed.
     */
    public function findFlaggedServiceConsumed(): Collection;

    /**
     * Advanced filter search used by reports and admin views.
     */
    public function search(
        ?string $reference = null,
        ?int $departmentId = null,
        ?int $requesterId = null,
        ?string $status = null,
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator;
}
