<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Console\FilterMakeCommand;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\Pipeline;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\AuthorizeWhenResolved;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->bootPublishes();

        $this->registerMacros();

        $this->app->afterResolving(AuthorizeWhenResolved::class, static function ($resolved) {
            $resolved->authorizeResolved();
        });
    }

    public function register(): void
    {
        $this->registerBindings();

        $this->registerConsole();
    }

    protected function bootPublishes(): void
    {
        $configPath = $this->configPath();

        $this->mergeConfigFrom($configPath, 'eloquent-builder');

        $this->publishes([$configPath => config_path('eloquent-builder.php')], 'config');
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/eloquent-builder.php';
    }

    protected function registerConsole(): void
    {
        $this->commands(FilterMakeCommand::class);
    }

    private function registerBindings(): void
    {
        $this->app->singleton('eloquentbuilder', fn (Application $app) => new EloquentBuilder($app->make(Pipeline::class)));
    }

    private function registerMacros(): void
    {
        Collection::macro('getFilters', function () {
            $filters = $this->filter(static function ($value, $filter) {
                if (! is_array($value)) {
                    return ! is_int($filter) && (isset($value) && strlen($value) !== 0);
                }

                $result = [];
                array_walk_recursive(
                    $value,
                    static function ($val) use (&$result) {
                        if (isset($val) && strlen($val) !== 0) {
                            $result[] = $val;
                        }
                    }
                );

                return ! empty($result);
            });

            return $filters->all();
        });
    }
}
