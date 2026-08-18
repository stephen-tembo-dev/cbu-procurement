<?php

namespace App\Repositories;

use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Models\Finance\BudgetAllocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BudgetAllocationRepository extends BaseRepository implements BudgetAllocationRepositoryInterface
{
    protected function model(): string
    {
        return BudgetAllocation::class;
    }

    public function findByCostCentreAndYear(int $costCentreId, int $fiscalYear): ?BudgetAllocation
    {
        return $this->model
            ->where('cost_centre_id', $costCentreId)
            ->where('fiscal_year', $fiscalYear)
            ->first();
    }

    public function findCurrentYear(int $costCentreId): ?BudgetAllocation
    {
        return $this->findByCostCentreAndYear($costCentreId, now()->year);
    }

    public function findAllForYear(int $fiscalYear): Collection
    {
        return $this->model
            ->with(['costCentre.department'])
            ->where('fiscal_year', $fiscalYear)
            ->orderBy('cost_centre_id')
            ->get();
    }

    public function incrementCommitted(int $id, float $amount): bool
    {
        return (bool) DB::transaction(function () use ($id, $amount) {
            return $this->model
                ->lockForUpdate()
                ->where('id', $id)
                ->increment('committed_amount', $amount);
        });
    }

    public function decrementCommitted(int $id, float $amount): bool
    {
        return (bool) DB::transaction(function () use ($id, $amount) {
            return $this->model
                ->lockForUpdate()
                ->where('id', $id)
                ->decrement('committed_amount', $amount);
        });
    }

    public function incrementSpent(int $id, float $amount): bool
    {
        return (bool) DB::transaction(function () use ($id, $amount) {
            return $this->model
                ->lockForUpdate()
                ->where('id', $id)
                ->increment('spent_amount', $amount);
        });
    }
}
