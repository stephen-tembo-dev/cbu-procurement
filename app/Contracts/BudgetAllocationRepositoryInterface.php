<?php

namespace App\Contracts;

use App\Models\Finance\BudgetAllocation;
use Illuminate\Database\Eloquent\Collection;

interface BudgetAllocationRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCostCentreAndYear(int $costCentreId, int $fiscalYear): ?BudgetAllocation;

    public function findCurrentYear(int $costCentreId): ?BudgetAllocation;

    /** All allocations for a given fiscal year, with cost centre and department. */
    public function findAllForYear(int $fiscalYear): Collection;

    /**
     * Atomically increment committed_amount.
     * Use DB lock to prevent race conditions on concurrent approvals.
     */
    public function incrementCommitted(int $id, float $amount): bool;

    public function decrementCommitted(int $id, float $amount): bool;

    public function incrementSpent(int $id, float $amount): bool;
}
