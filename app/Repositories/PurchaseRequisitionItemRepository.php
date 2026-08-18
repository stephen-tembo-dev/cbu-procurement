<?php

namespace App\Repositories;

use App\Contracts\PurchaseRequisitionItemRepositoryInterface;
use App\Models\Procurement\PurchaseRequisitionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseRequisitionItemRepository extends BaseRepository implements PurchaseRequisitionItemRepositoryInterface
{
    protected function model(): string
    {
        return PurchaseRequisitionItem::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('stockItem')
            ->where('purchase_requisition_id', $prId)
            ->get();
    }

    public function syncItems(int $prId, array $items): Collection
    {
        return DB::transaction(function () use ($prId, $items) {
            $this->model->where('purchase_requisition_id', $prId)->delete();

            foreach ($items as $item) {
                $item['purchase_requisition_id'] = $prId;
                $item['total_price_estimated']   = $item['quantity'] * $item['unit_price_estimated'];
                $this->model->create($item);
            }

            return $this->findByRequisition($prId);
        });
    }

    public function recalculateTotal(int $itemId): PurchaseRequisitionItem
    {
        $item = $this->model->findOrFail($itemId);
        $item->update([
            'total_price_estimated' => $item->quantity * $item->unit_price_estimated,
        ]);

        return $item->fresh();
    }
}
