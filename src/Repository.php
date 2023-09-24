<?php

declare(strict_types=1);

namespace Manchenkoff\Laravel\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Manchenkoff\Laravel\Repositories\Contracts\RepositoryInterface;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelDeleteException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelSaveException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelUpdateException;
use Throwable;

/**
 * Basic abstract repository class with default implementation of interface methods.
 *
 * @template TKey of array-key
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @implements RepositoryInterface<TKey, TModel>
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * Model class name string.
     *
     * @var class-string<TModel>
     */
    protected static string $modelClass;

    public function paginated(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', int $page = null): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns, $pageName, $page);
    }

    public function all(): Collection
    {
        /** @var Collection<TKey, TModel> $collection */
        $collection = $this->query()->get();

        return $collection;
    }

    public function find(mixed $id): ?Model
    {
        return $this->query()->whereKey($id)->first();
    }

    public function get(mixed $id): Model
    {
        return $this->query()->whereKey($id)->firstOrFail();
    }

    public function create(array $data): Model
    {
        try {
            return $this->query()->create($data);
        } catch (Throwable $exception) {
            throw new ModelSaveException('Unable to create model in the database', previous: $exception);
        }
    }

    public function update(Model $entity, array $data): Model
    {
        try {
            $entity->updateOrFail($data);
        } catch (Throwable $exception) {
            throw new ModelUpdateException('Unable to update model in the database', previous: $exception);
        }

        return $entity;
    }

    public function updateMany(array $ids, array $data): void
    {
        DB::beginTransaction();

        try {
            $affected = $this->query()->whereKey($ids)->update($data);

            if ($affected !== count($ids)) {
                throw new ModelUpdateException('Unable to update one or many models in the database');
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            if (!$exception instanceof ModelUpdateException) {
                throw new ModelUpdateException('Unable to update one or many models in the database', previous: $exception);
            }

            throw $exception;
        }
    }

    public function delete(Model $entity): Model
    {
        try {
            $entity->deleteOrFail();
        } catch (Throwable $exception) {
            throw new ModelDeleteException('Unable to delete model from the database', previous: $exception);
        }

        return $entity;
    }

    public function deleteMany(array $ids): void
    {
        DB::beginTransaction();

        try {
            $deleted = $this->query()->whereKey($ids)->delete();

            if ($deleted !== count($ids)) {
                throw new ModelDeleteException('Unable to delete one or many models from the database');
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * Initial query builder used in repository methods.
     *
     * @return Builder
     */
    protected function query(): Builder
    {
        return static::$modelClass::query();
    }
}
