<?php

namespace App\Repositories;

use App\Contracts\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    protected function model(): string
    {
        return Department::class;
    }

    public function findByCode(string $code): ?Department
    {
        return $this->model->where('code', $code)->first();
    }

    public function findActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findWithRelations(int $id): ?Department
    {
        return $this->model
            ->with(['hod', 'costCentres', 'users' => fn ($q) => $q->where('is_active', true)])
            ->find($id);
    }

    public function assignHod(int $departmentId, int $userId): bool
    {
        return (bool) $this->model
            ->where('id', $departmentId)
            ->update(['hod_user_id' => $userId]);
    }
}
