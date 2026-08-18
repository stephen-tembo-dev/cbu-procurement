<?php

namespace App\Repositories;

use App\Contracts\PaymentRepositoryInterface;
use App\Models\Finance\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    protected function model(): string
    {
        return Payment::class;
    }

    public function findByPurchaseOrder(int $poId): ?Payment
    {
        return $this->model
            ->with(['vcApprover', 'bursarConfirmer', 'purchaseOrder.supplier'])
            ->where('purchase_order_id', $poId)
            ->first();
    }

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition.department', 'purchaseOrder.supplier'])
            ->when(
                is_array($status),
                fn ($q) => $q->whereIn('status', $status),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findPendingVcApproval(): Collection
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition', 'purchaseOrder.supplier'])
            ->where('status', 'pending')
            ->whereNull('vc_approved_at')
            ->get();
    }

    public function findPendingBursarConfirmation(): Collection
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition', 'purchaseOrder.supplier'])
            ->where('status', 'pending')
            ->whereNotNull('vc_approved_at')
            ->whereNull('bursar_confirmed_at')
            ->get();
    }

    public function findDelayed(): Collection
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition', 'purchaseOrder.supplier'])
            ->whereNotNull('delay_comment')
            ->where('status', 'pending')
            ->get();
    }

    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition.requester', 'purchaseOrder.supplier', 'vcApprover', 'bursarConfirmer'])
            ->when($status,   fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate($perPage);
    }
}
