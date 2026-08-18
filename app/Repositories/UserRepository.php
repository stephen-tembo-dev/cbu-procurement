<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection
    {
        return $this->model
            ->where('department_id', $departmentId)
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    public function findByRole(string $roleName): Collection
    {
        return $this->model
            ->role($roleName)           // Spatie scope
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function paginate(
        int $perPage = 20,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['department', 'roles'])
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn ($q) => $q->role($role))
            ->orderBy('name');

        return $query->paginate($perPage);
    }

    public function setActive(int $id, bool $active): bool
    {
        return (bool) $this->model->where('id', $id)->update(['is_active' => $active]);
    }
}
