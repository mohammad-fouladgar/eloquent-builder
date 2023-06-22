<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\ServiceProvider;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected EloquentBuilder $eloquentBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Fouladgar\\EloquentBuilder\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->eloquentBuilder = new EloquentBuilder(app(Pipeline::class));
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('eloquent-builder.namespace', 'Fouladgar\\EloquentBuilder\\Tests\\EloquentFilters\\');
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }
}
