# Laravel Repositories

Package provides a basic implementation of Repository pattern with `artisan` command to generate classes.

Features:

-   `Repository` class with basic methods like `all`, `find`, `create`, `update`, `delete`
-   Generic type comments to pass `PHPStan` checks
-   Artisan `make:repository` command to generate repository class with model and interface

## Installation

To install this package, you need to install [Composer](https://getcomposer.org/) first, and then run:

```bash
composer require manchenkoff/laravel-repositories
```

or add this line to `composer.json`:

```json
"manchenkoff/laravel-repositories": "*"
```

and run `composer update` in the terminal.

Package should automatically register its service provider in your application, but you can do it manually in `config/app.php`:

```php
'providers' => ServiceProvider::defaultProviders()
    ->merge([
        // Package Service Providers
        \Manchenkov\Laravel\Repositories\ServiceProvider::class,

        // Application Service Providers
        // ...
    ])
    ->toArray(),
```

## Usage

First of all, you need to create a model class for your repository. You can do it manually or use `artisan` command:

```bash
php artisan make:model Post
```

Then you can create a repository class for your model:

```bash
# repository name - PostRepository
# model name - Post
php artisan make:repository PostRepository Post
```

This command will create a repository class in `app/Repositories` directory and `PostRepositoryInterface` contract class in `app/Contracts/Repositories`.

Now you can use existing methods in your services or extend with custom functionality:

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\PostRepositoryInterface;
use App\Contracts\Services\PostServiceInterface;

final class PostService implements PostServiceInterface
{
    private readonly PostRepositoryInterface $repository;

    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllPosts(): Collection
    {
        return $this->repository->all();
    }
}
```

## Implementation

All repository methods use protected `query()` method to get `Eloquent\Builder` instance. You can override this method in your repository class to add custom logic, e.g. when you always need some relations to be loaded or custom sorting applied.

```php
protected function query(): Builder
{
    return parent::query()->with('comments')->orderBy('created_at', 'desc');
}
```

Here is a list of available methods with a quick description:

| Method                                      | Description                                             |
| ------------------------------------------- | ------------------------------------------------------- |
| `paginated(): LengthAwarePaginator`         | returns paginated collection                            |
| `all(): Collection`                         | returns all entities                                    |
| `find(mixed $id): ?Model`                   | returns entity by id or null                            |
| `get(mixed $id): Model`                     | returns entity by id or throws `ModelNotFoundException` |
| `create(array $data): Model`                | creates new entity with given data                      |
| `update(Model $entity, array $data): Model` | updates existing entity with given data                 |
| `updateMany(array $ids, array $data): void` | updates many entities with given data by ids            |
| `delete(Model $entity): Model`              | deletes existing entity                                 |
| `deleteMany(array $ids): void`              | deletes many entities by ids                            |

## Development

This package is completely open-source, so any contributions are welcome!

Clone this repository to your local machine, install dependencies and run tests:

```bash
git clone https://github.com/manchenkoff/laravel-repositories
cd laravel-repositories
composer install
composer test
```

There are some useful `composer` scripts:

| Script                | Description                                    |
| --------------------- | ---------------------------------------------- |
| `composer fmt`        | Apply Laravel Pint code style rules            |
| `composer test`       | Run tests with Testbench package               |
| `composer lint`       | Run PHP Stan analysis against package codebase |
| `composer rector`     | Run Rector analysis against package codebase   |
| `composer rector:fix` | Apply available Rector suggestions             |
