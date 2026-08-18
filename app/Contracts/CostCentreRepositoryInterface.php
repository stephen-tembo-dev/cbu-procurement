<?php

namespace App\Contracts;

use App\Models\CostCentre;
use Illuminate\Database\Eloquent\Collection;

interface CostCentreRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?CostCentre;

    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection;

    /** Return cost centres with their current-year budget allocation. */
    public function findWithCurrentBudget(int $departmentId): Collection;
}
