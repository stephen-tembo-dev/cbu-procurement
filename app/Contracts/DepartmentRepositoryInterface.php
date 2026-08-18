<?php

namespace App\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Department;

    public function findActive(): Collection;

    /** Return department with its cost centres and HOD eager-loaded. */
    public function findWithRelations(int $id): ?Department;

    /** Assign (or re-assign) the HOD for a department. */
    public function assignHod(int $departmentId, int $userId): bool;
}
