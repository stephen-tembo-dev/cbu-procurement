<?php

namespace App\Repositories;

use App\Contracts\CostCentreRepositoryInterface;
use App\Models\CostCentre;
use Illuminate\Database\Eloquent\Collection;

class CostCentreRepository extends BaseRepository implements CostCentreRepositoryInterface
{
    protected function model(): string
    {
        return CostCentre::class;
    }

    public function findByCode(string $code): ?CostCentre
    {
        return $this->model->where('code', $code)->first();
    }

    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection
    {
        return $this->model
            ->where('department_id', $departmentId)
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('code')
            ->get();
    }

    public function findWithCurrentBudget(int $departmentId): Collection
    {
        return $this->model
            ->with(['budgetAllocations' => fn ($q) => $q->where('fiscal_year', now()->year)])
            ->where('department_id', $departmentId)
            ->where('is_active', true)
            ->get();
    }
}
