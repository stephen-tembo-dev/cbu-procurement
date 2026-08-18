<?php

namespace App\Repositories;

use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\Procurement\PurchaseRequisition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseRequisitionRepository extends BaseRepository implements PurchaseRequisitionRepositoryInterface
{
    protected function model(): string
    {
        return PurchaseRequisition::class;
    }

    public function findByReference(string $reference): ?PurchaseRequisition
    {
        return $this->model->where('reference_no', $reference)->first();
    }

    public function findWithFullTrail(int $id): ?PurchaseRequisition
    {
        return $this->model->with([
            'requester',
            'department',
            'costCentre.budgetAllocations' => fn ($q) => $q->where('fiscal_year', now()->year),
            'items.stockItem',
            'approvalRecords.actor',
            'supplierQuotes.supplier',
            'purchaseOrder.supplier',
            'purchaseOrder.items',
            'purchaseOrder.payment.vcApprover',
            'purchaseOrder.payment.bursarConfirmer',
            'attachments.uploader',
        ])->find($id);
    }

    public function findByRequester(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['department', 'costCentre'])
            ->where('requester_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByDepartment(int $departmentId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['requester', 'costCentre'])
            ->where('department_id', $departmentId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['requester', 'department', 'costCentre'])
            ->when(
                is_array($status),
                fn ($q) => $q->whereIn('status', $status),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findPendingForUser(int $userId, string $role): LengthAwarePaginator
    {
        $statusMap = [
            'hod'                 => ['pending_hod'],
            'stores'              => ['pending_stores'],
            'bursar'              => ['pending_bursar'],
            'vc'                  => ['pending_vc_requisition', 'pending_vc_payment'],
            'procurement'         => ['pending_procurement'],
            'auditor'             => ['pending_audit'],
        ];

        $statuses = $statusMap[$role] ?? [];

        return $this->model
            ->with(['requester', 'department', 'costCentre'])
            ->when(
                $role === 'hod',
                // HOD only sees their own department's PRs
                fn ($q) => $q->whereHas('department', fn ($d) => $d->where('hod_user_id', $userId))
            )
            ->when(
                !empty($statuses),
                fn ($q) => $q->whereIn('status', $statuses)
            )
            ->latest()
            ->paginate(20);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return (bool) $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function findFlaggedServiceConsumed(): Collection
    {
        return $this->model
            ->with(['requester', 'department'])
            ->where('service_consumed_flag', true)
            ->whereNotIn('status', ['rejected', 'suspended', 'delivered'])
            ->latest()
            ->get();
    }

    public function search(
        ?string $reference = null,
        ?int $departmentId = null,
        ?int $requesterId = null,
        ?string $status = null,
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->model
            ->with(['requester', 'department', 'costCentre'])
            ->when($reference,    fn ($q) => $q->where('reference_no', 'like', "%{$reference}%"))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->when($requesterId,  fn ($q) => $q->where('requester_id', $requesterId))
            ->when($status,       fn ($q) => $q->where('status', $status))
            ->when($type,         fn ($q) => $q->where('type', $type))
            ->when($dateFrom,     fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,       fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate($perPage);
    }
}
