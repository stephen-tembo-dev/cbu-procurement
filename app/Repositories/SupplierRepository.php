<?php

namespace App\Repositories;

use App\Contracts\SupplierRepositoryInterface;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    protected function model(): string
    {
        return Supplier::class;
    }

    public function findActive(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('name')->get();
    }

    public function findZppaApproved(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where('zppa_approved', true)
            ->orderBy('name')
            ->get();
    }

    public function search(string $term, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('contact_email', 'like', "%{$term}%")
                  ->orWhere('zppa_registration', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getQuoteCount(int $supplierId): int
    {
        return $this->model->find($supplierId)?->quotes()->count() ?? 0;
    }
}
