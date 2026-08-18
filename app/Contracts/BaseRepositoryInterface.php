<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Find a single record by primary key.
     * Returns null if not found (never throws).
     */
    public function findById(int $id, array $relations = []): ?Model;

    /**
     * Find a single record by a given column value.
     */
    public function findBy(string $column, mixed $value, array $relations = []): ?Model;

    /**
     * Find all records matching a key→value criteria array.
     */
    public function findWhere(array $criteria, array $relations = [], ?string $orderBy = null, string $direction = 'asc'): Collection;

    /**
     * Return a paginated or full collection.
     * Pass $perPage = null for a full Collection; an integer for paginated results.
     */
    public function all(array $relations = [], ?int $perPage = null, ?string $orderBy = null, string $direction = 'desc'): Collection|LengthAwarePaginator;

    /**
     * Persist a new record and return the hydrated model.
     */
    public function create(array $data): Model;

    /**
     * Update a record by primary key. Returns the updated model.
     * Throws ModelNotFoundException if the record does not exist.
     */
    public function update(int $id, array $data): Model;

    /**
     * Soft-delete (or hard-delete if model has no SoftDeletes) by primary key.
     */
    public function delete(int $id): bool;

    /**
     * Check whether a record exists without loading it.
     */
    public function exists(int $id): bool;

    /**
     * Return count of records matching optional criteria.
     */
    public function count(array $criteria = []): int;
}
