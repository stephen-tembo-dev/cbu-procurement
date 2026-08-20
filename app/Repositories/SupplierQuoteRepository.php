<?php

namespace App\Repositories;

use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Models\Procurement\SupplierQuote;
use Illuminate\Database\Eloquent\Collection;

class SupplierQuoteRepository extends BaseRepository implements SupplierQuoteRepositoryInterface
{
    protected function model(): string
    {
        return SupplierQuote::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->orderBy('amount')
            ->get();
    }

    public function existsForRequisitionAndSupplier(int $prId, int $supplierId): bool
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->where('supplier_id', $supplierId)
            ->exists();
    }

    public function findResponded(int $prId): Collection
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->orderBy('amount')
            ->get();
    }

    public function findNoResponse(int $prId): Collection
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->whereNull('amount')
            ->where('supplier_contacted', true)
            ->get();
    }

    public function findLowestQuote(int $prId): ?SupplierQuote
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->orderBy('amount')
            ->first();
    }

    public function allQuotesExceedThreshold(int $prId, float $threshold): bool
    {
        $respondedCount = $this->model
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->count();

        if ($respondedCount === 0) {
            return false;
        }

        $exceedingCount = $this->model
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->where('amount', '>', $threshold)
            ->count();

        return $exceedingCount === $respondedCount;
    }
}
