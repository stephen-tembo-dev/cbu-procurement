<?php

namespace App\Contracts;

use App\Models\StockItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockItemRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStockCode(string $code): ?StockItem;

    /** Items that ARE stocked — users cannot order these directly. */
    public function findStocked(): Collection;

    /** Items users CAN directly request (is_stocked = false). */
    public function findOrderable(): Collection;

    /** Items whose quantity_on_hand is at or below reorder_level. */
    public function findBelowReorderLevel(): Collection;

    /** Adjust stock quantity. Pass positive delta to add, negative to deduct. */
    public function adjustQuantity(int $id, float $delta): bool;

    public function search(string $term, ?int $perPage = 20): LengthAwarePaginator;
}
