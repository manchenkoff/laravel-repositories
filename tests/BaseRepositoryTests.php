<?php

declare(strict_types=1);

namespace Manchenkoff\Laravel\Repositories\Tests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Manchenkoff\Laravel\Repositories\Contracts\RepositoryInterface;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelDeleteException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelSaveException;
use Manchenkoff\Laravel\Repositories\Exceptions\ModelUpdateException;
use Manchenkoff\Laravel\Repositories\Repository;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;
use Workbench\App\Models\User;
use Workbench\App\Repositories\UserRepository;
use Workbench\App\Repositories\UserRepositoryInterface;
use Workbench\Database\Seeders\UserSeeder;

final class BaseRepositoryTests extends TestCase
{
    use RefreshDatabase, WithWorkbench;

    protected bool $seed = true;

    protected string $seeder = UserSeeder::class;

    private UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = new UserRepository();

        parent::setUp();
    }

    public function test_instance_has_correct_class(): void
    {
        $this->assertInstanceOf(RepositoryInterface::class, $this->repository);
        $this->assertInstanceOf(Repository::class, $this->repository);
        $this->assertInstanceOf(UserRepositoryInterface::class, $this->repository);
    }

    public function test_repository_returns_paginated_result(): void
    {
        $expected = User::paginate();
        $actual = $this->repository->paginated();

        $this->assertEquals($expected, $actual);
    }

    public function test_repository_returns_all_records(): void
    {
        $expected = User::all();
        $actual = $this->repository->all();

        $this->assertEquals($expected, $actual);
    }

    public function test_repository_finds_correct_record(): void
    {
        $expected = User::all()->random();
        $actual = $this->repository->find($expected->id);

        $this->assertEquals($expected, $actual);
    }

    public function test_repository_finds_no_record(): void
    {
        $actual = $this->repository->find(0);

        $this->assertNull($actual);
    }

    public function test_repository_gets_correct_record(): void
    {
        $expected = User::all()->random();
        $actual = $this->repository->get($expected->id);

        $this->assertEquals($expected, $actual);
    }

    public function test_repository_gets_no_record(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->get(0);
    }

    public function test_repository_creates_record(): void
    {
        $fields = [
            'name' => 'Test User',
            'email' => 'john@doe.com',
            'password' => 'secret',
        ];

        $this->repository->create($fields);

        $this->assertDatabaseHas('users', $fields);
    }

    public function test_repository_creates_record_with_exception(): void
    {
        $fields = [
            'name' => 'Test User',
            'email' => 'john@doe.com',
            'password' => 'secret',
        ];

        $this->repository->create($fields);

        $this->assertDatabaseHas('users', $fields);

        $this->expectException(ModelSaveException::class);

        $this->repository->create($fields);
    }

    public function test_repository_updates_record(): void
    {
        $user = User::all()->random();

        $this->repository->update($user, ['name' => 'Test User']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Test User']);
    }

    public function test_repository_updates_record_with_exception(): void
    {
        $user = User::all()->random();
        $anotherUser = User::all()->whereNotIn('email', [$user->email])->random();

        $this->expectException(ModelUpdateException::class);

        $this->repository->update($user, ['email' => $anotherUser->email]);
    }

    public function test_repository_updates_many_records(): void
    {
        $users = User::all()->random(2);

        $this->repository->updateMany(
            $users->pluck('id')->toArray(),
            ['password' => 'new_password']
        );

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', ['id' => $user->id, 'password' => 'new_password']);
        }
    }

    public function test_repository_updates_many_records_several_times(): void
    {
        $users = User::all()->random(2);

        $this->repository->updateMany(
            $users->pluck('id')->toArray(),
            ['password' => 'new_password']
        );

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', ['id' => $user->id, 'password' => 'new_password']);
        }

        $this->repository->updateMany(
            $users->pluck('id')->toArray(),
            ['password' => 'new_password']
        );
    }

    public function test_repository_updates_many_records_with_exception(): void
    {
        $users = User::all();

        $this->expectException(ModelUpdateException::class);

        $this->repository->updateMany(
            $users->pluck('id')->toArray(),
            ['email' => 'unique@email.com']
        );
    }

    public function test_repository_deletes_record(): void
    {
        $user = User::all()->random();

        $this->repository->delete($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_repository_deletes_many_records(): void
    {
        $users = User::all()->random(2);

        $this->repository->deleteMany($users->pluck('id')->toArray());

        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        }
    }

    public function test_repository_deletes_many_records_with_exception(): void
    {
        $ids = [1, 999];

        $this->expectException(ModelDeleteException::class);

        $this->repository->deleteMany($ids);
    }
}
