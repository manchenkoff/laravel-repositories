<?php

declare(strict_types=1);

namespace Manchenkoff\Laravel\Repositories\Commands;

use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:repository:contract')]
final class RepositoryContractMakeCommand extends GeneratorCommand
{
    protected $name = 'make:repository:contract';

    protected $description = 'Create a new repository interface';

    protected $type = 'Contract';

    public function handle(): ?bool
    {
        if (!str_ends_with($this->getNameInput(), 'Interface')) {
            $this->error('The repository contract name must end with "Interface" suffix!');

            return false;
        }

        if (parent::handle() === false) {
            return false;
        }

        return null;
    }

    protected function getStub(): string
    {
        $stub = '/stubs/repository.contract.stub';

        return $this->resolveStubPath($stub);
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Contracts\Repositories';
    }

    protected function buildClass($name): string
    {
        $replace = [];
        $replace = $this->buildModelReplacements($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }

    /**
     * @return array<array-key, array<string, string>>
     */
    protected function getArguments(): array
    {
        $parent = parent::getArguments();

        return [
            ...$parent,
            ['model', InputArgument::REQUIRED, 'The name of the model'],
        ];
    }

    private function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    /**
     * @param  array<string, string>  $replace
     *
     * @return array<string, string>
     */
    private function buildModelReplacements(array $replace)
    {
        /** @var string $model */
        $model = $this->argument('model');
        $modelClass = $this->parseModel($model);

        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist!");
            exit;
        }

        return array_merge($replace, [
            '{{ namespacedModel }}' => $modelClass,
            '{{ model }}' => class_basename($modelClass),
        ]);
    }

    private function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }
}
