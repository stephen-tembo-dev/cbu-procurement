<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    /** All active users belonging to a department. */
    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection;

    /** All users carrying a given Spatie role name. */
    public function findByRole(string $roleName): Collection;

    /**
     * Paginated user list with optional search on name/email
     * and optional role filter.
     */
    public function paginate(
        int $perPage = 20,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator;

    /** Toggle the is_active flag. */
    public function setActive(int $id, bool $active): bool;
}
