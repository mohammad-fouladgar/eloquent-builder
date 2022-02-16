<?php

namespace Fouladgar\EloquentBuilder\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class FilterMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eloquent-builder:make
                            {model : Name of the model which filter will build upon}
                            {name* : Filter name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new EloquentBuilder filter';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Filter[s]';

    /**
     * The name of model which filter will build upon.
     *
     * @var string
     */
    protected $model;

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $this->setModel($this->argument('model'));

        $filters = $this->argument('name');

        foreach ($filters as $filter) {
            $filter = $this->sanitizeNameInput($filter);
            $name = $this->qualifyClass($filter);
            $path = $this->getPath($name);
            $this->makeDirectory($path);
            $this->files->put($path, $this->buildClass($name));
        }

        $this->info($this->type.' created successfully.');
    }

    /**
     * @param $name
     */
    protected function setModel($name): void
    {
        $this->model = Str::ucfirst(Str::lower($name));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/filter.stub';
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return rtrim(config('eloquent-builder.namespace'), '\\').'\\'.$this->model;
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function sanitizeNameInput($name): string
    {
        return Str::studly(trim($name)).'Filter';
    }
}
