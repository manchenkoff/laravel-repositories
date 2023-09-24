<?php

declare(strict_types=1);

namespace Workbench\App\Repositories;

use Manchenkoff\Laravel\Repositories\Contracts\RepositoryInterface;
use Workbench\App\Models\User;

/**
 * @template TKey of array-key
 * @template TModel of User
 *
 * @extends RepositoryInterface<TKey, TModel>
 */
interface UserRepositoryInterface extends RepositoryInterface
{
}
