<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->model());
    }

    /**
     * Return the fully-qualified model class name.
     * Concrete repositories must implement this.
     */
    abstract protected function model(): string;

    // ── BaseRepositoryInterface implementation ─────────────────────────────

    public function findById(int $id, array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id);
    }

    public function findBy(string $column, mixed $value, array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($column, $value)->first();
    }

    public function findWhere(
        array $criteria,
        array $relations = [],
        ?string $orderBy = null,
        string $direction = 'asc'
    ): Collection {
        $query = $this->model->with($relations)->where($criteria);

        if ($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        return $query->get();
    }

    public function all(
        array $relations = [],
        ?int $perPage = null,
        ?string $orderBy = null,
        string $direction = 'desc'
    ): Collection|LengthAwarePaginator {
        $query = $this->model->with($relations);

        if ($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->model->findOrFail($id);

        return (bool) $record->delete();
    }

    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function count(array $criteria = []): int
    {
        return empty($criteria)
            ? $this->model->count()
            : $this->model->where($criteria)->count();
    }

    // ── Convenience helpers available to all concrete repositories ─────────

    protected function newQuery()
    {
        return $this->model->newQuery();
    }
}
