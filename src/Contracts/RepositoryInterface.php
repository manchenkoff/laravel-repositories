<?php

declare(strict_types=1);

namespace Manchenkoff\Laravel\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelDeleteException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelSaveException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelUpdateException;
use Throwable;

/**
 * @template TKey of array-key
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * Returns a paginated list of entities.
     *
     * @param  int  $perPage  Number of entities per page
     * @param  array<string>  $columns  List of columns to select
     * @param  string  $pageName  Name of the page parameter
     * @param  int|null  $page  Current page number (null = resolve automatically)
     *
     * @return LengthAwarePaginator<TKey, TModel|Model>
     */
    public function paginated(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator;

    /**
     * Returns a list of all entities.
     *
     * @return Collection<TKey, TModel|Model>
     */
    public function all(): Collection;

    /**
     * Returns a specific entity by identifier.
     *
     * @param  mixed  $id  Entity identifier
     *
     * @return Model|TModel|null
     */
    public function find(mixed $id): ?Model;

    /**
     * Returns a specific entity by identifier or throws an exception.
     *
     * @param  mixed  $id  Entity identifier
     *
     * @return Model|TModel
     *
     * @throws ModelNotFoundException if entity was not found
     */
    public function get(mixed $id): Model;

    /**
     * Creates a new entity with passed key-values array.
     *
     * @param  array<string, mixed>  $data  Attributes to assign to the new entity
     *
     * @return Model|TModel
     *
     * @throws ModelSaveException if entity was not created
     */
    public function create(array $data): Model;

    /**
     * Updates an existing entity with passed key-values array.
     *
     * @param  Model|TModel  $entity  Entity to update
     * @param  array<string, mixed>  $data  Attributes to assign to the specified entity
     *
     * @return Model|TModel
     *
     * @throws ModelUpdateException if entity was not updated
     */
    public function update(Model $entity, array $data): Model;

    /**
     * Updates many entities by their identifiers with passed key-values array.
     *
     * @param  array<mixed>  $ids  Identifiers of entities to update
     * @param  array<string, mixed>  $data  Attributes to assign to each entity
     *
     * @throws ModelUpdateException if entities were not updated
     */
    public function updateMany(array $ids, array $data): void;

    /**
     * Deletes an existing entity.
     *
     * @param  Model|TModel  $entity  Entity to delete from the database
     *
     * @return Model|TModel
     *
     * @throws ModelDeleteException if the entity was not deleted
     */
    public function delete(Model $entity): Model;

    /**
     * Deletes entities by their identifiers.
     *
     * @param  array<mixed>  $ids  Identifiers of entities to delete
     *
     * @throws ModelDeleteException if entities were not deleted
     * @throws Throwable
     */
    public function deleteMany(array $ids): void;
}
