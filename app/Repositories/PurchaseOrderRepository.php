<?php

namespace App\Repositories;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseOrderRepository extends BaseRepository implements PurchaseOrderRepositoryInterface
{
    protected function model(): string
    {
        return PurchaseOrder::class;
    }

    public function findByPoNumber(string $poNumber): ?PurchaseOrder
    {
        return $this->model->where('po_number', $poNumber)->with(['purchaseRequisition', 'supplier'])->first();
    }

    public function findByRequisition(int $prId): ?PurchaseOrder
    {
        return $this->model
            ->with(['supplier', 'items', 'payment'])
            ->where('purchase_requisition_id', $prId)
            ->first();
    }

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['purchaseRequisition.department', 'supplier'])
            ->when(
                is_array($status),
                fn ($q) => $q->whereIn('status', $status),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findUndelivered(): Collection
    {
        return $this->model
            ->with(['purchaseRequisition.department', 'supplier'])
            ->whereIn('status', ['issued', 'partially_delivered'])
            ->orderBy('issued_date')
            ->get();
    }

    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->model
            ->with(['purchaseRequisition.requester', 'purchaseRequisition.department', 'supplier', 'payment'])
            ->when($status,   fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->whereDate('issued_date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('issued_date', '<=', $dateTo))
            ->latest('issued_date')
            ->paginate($perPage);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return (bool) $this->model->where('id', $id)->update(['status' => $status]);
    }
}
