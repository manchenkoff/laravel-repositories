<?php

declare(strict_types=1);

namespace Manchenkoff\Laravel\Repositories\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:repository')]
final class RepositoryClassMakeCommand extends GeneratorCommand
{
    protected $name = 'make:repository';

    protected $description = 'Create a new repository class';

    protected $type = 'Repository';

    public function handle(): ?bool
    {
        if (!str_ends_with($this->getNameInput(), 'Repository')) {
            $this->error('The repository name must end with "Repository" suffix!');

            return false;
        }

        if (parent::handle() === false) {
            return false;
        }

        $this->createInterface();

        return null;
    }

    protected function getStub(): string
    {
        $stub = '/stubs/repository.class.stub';

        return $this->resolveStubPath($stub);
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Repositories';
    }

    protected function buildClass($name): string
    {
        $replace = [];
        $replace = $this->buildModelReplacements($replace);
        $replace = $this->buildContractReplacements($replace);

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
    private function buildModelReplacements(array $replace): array
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

    /**
     * @param  array<string, string>  $replace
     *
     * @return array<string, string>
     */
    private function buildContractReplacements(array $replace): array
    {
        $contractName = $this->getNameInput() . 'Interface';
        $contractClass = $this->qualifyContract($contractName);

        return array_merge($replace, [
            '{{ namespacedContract }}' => $contractClass,
            '{{ contract }}' => class_basename($contractClass),
        ]);
    }

    private function qualifyContract(string $contract): string
    {
        $contract = ltrim($contract, '\\/');

        $contract = str_replace('/', '\\', $contract);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($contract, $rootNamespace)) {
            return $contract;
        }

        return $rootNamespace . 'Contracts\\Repositories\\' . $contract;
    }

    private function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    private function createInterface(): void
    {
        $this->call('make:repository:contract', [
            'name' => $this->getNameInput(),
            'model' => $this->argument('model'),
        ]);
    }
}
