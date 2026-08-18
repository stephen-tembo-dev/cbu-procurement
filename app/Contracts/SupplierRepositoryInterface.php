<?php

namespace App\Contracts;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function findActive(): Collection;

    public function findZppaApproved(): Collection;

    public function search(string $term, int $perPage = 20): LengthAwarePaginator;

    /** How many quotes has this supplier submitted across all PRs? */
    public function getQuoteCount(int $supplierId): int;
}
