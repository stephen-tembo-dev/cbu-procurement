<?php

namespace App\Contracts;

use App\Models\Procurement\PurchaseRequisitionItem;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRequisitionItemRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRequisition(int $prId): Collection;

    /** Replace all items on a PR with a new set. */
    public function syncItems(int $prId, array $items): Collection;

    /** Recalculate and persist total_price_estimated for an item. */
    public function recalculateTotal(int $itemId): PurchaseRequisitionItem;
}
