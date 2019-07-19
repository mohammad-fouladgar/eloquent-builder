<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\AuthorizeWhenResolved;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\FilterFactory as Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->bootPublishes();

        $this->registerMacros();

        $this->app->afterResolving(AuthorizeWhenResolved::class, function ($resolved) {
            $resolved->authorizeResolved();
        });
    }

    /*
     * Register bindings in the container.
     */
    public function register()
    {
        $this->registerBindings();

        $this->registerConsole();
    }

    protected function bootPublishes()
    {
        $configPath = $this->configPath();

        $this->mergeConfigFrom($configPath, 'eloquent-builder');

        $this->publishes([
            $configPath => config_path('eloquent-builder.php'),
        ], 'config');
    }

    protected function configPath()
    {
        return __DIR__.'/../config/eloquent-builder.php';
    }

    /**
     * Register console commands.
     */
    protected function registerConsole()
    {
        // commands...
    }

    private function registerBindings()
    {
        $this->app->bind(Factory::class, FilterFactory::class);

        $this->app->singleton('eloquentbuilder', function () {
            return new EloquentBuilder(new FilterFactory());
        });
    }

    private function registerMacros()
    {
        Collection::macro('getFilters', function () {
            $filters = $this->filter(function ($value, $filter) {
                return !is_int($filter) && '' !== $value && !is_null($value);
            });

            return $filters->all();
        });
    }
}
