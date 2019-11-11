<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\ServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->withFactories(__DIR__.'/database/factories');
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        // $app['config']->set('database.default', 'mysql');
        // $app['config']->set('database.connections.mysql', [
        //     'driver'       => 'mysql',
        //     'host'         => 'mysql',
        //     'port'         => '3306',
        //     'database'     => 'eloquent-builder',
        //     'username'     => 'root',
        //     'password'     => '',
        //     'charset'      => 'utf8mb4',
        //     'collation'    => 'utf8mb4_unicode_ci',
        //     'prefix'       => '',
        //     'strict'       => true,
        //     'engine'       => null,
        //     ]);

        $app['config']->set('eloquent-builder.namespace', 'Fouladgar\\EloquentBuilder\\Tests\\EloquentFilters\\');
    }

    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
