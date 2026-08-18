<?php

namespace App\Repositories;

use App\Contracts\StockItemRepositoryInterface;
use App\Models\StockItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StockItemRepository extends BaseRepository implements StockItemRepositoryInterface
{
    protected function model(): string
    {
        return StockItem::class;
    }

    public function findByStockCode(string $code): ?StockItem
    {
        return $this->model->where('stock_code', $code)->first();
    }

    public function findStocked(): Collection
    {
        return $this->model->stocked()->orderBy('stock_code')->get();
    }

    public function findOrderable(): Collection
    {
        return $this->model->orderable()->orderBy('stock_code')->get();
    }

    public function findBelowReorderLevel(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('stock_code')
            ->get();
    }

    public function adjustQuantity(int $id, float $delta): bool
    {
        return (bool) $this->model
            ->where('id', $id)
            ->increment('quantity_on_hand', $delta);
    }

    public function search(string $term, ?int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('stock_code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%");
            })
            ->orderBy('stock_code')
            ->paginate($perPage);
    }
}
