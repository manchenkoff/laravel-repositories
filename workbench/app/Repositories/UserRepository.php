<?php

declare(strict_types=1);

namespace Workbench\App\Repositories;

use Manchenkoff\Laravel\Repositories\Repository;
use Workbench\App\Models\User;

/**
 * @template TKey of array-key
 * @template TModel of User
 *
 * @implements UserRepositoryInterface<TKey, TModel>
 */
final class UserRepository extends Repository implements UserRepositoryInterface
{
    protected static string $modelClass = User::class;
}
