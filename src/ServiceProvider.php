<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/eloquent-builder.php' => config_path('eloquent-builder.php'),
        ], 'config');

        $this->registerMacros();
    }

    /*
     * Register bindings in the container.
     */
    public function register()
    {
        // Register bindings.
        $this->registerBindings();

        // Merge config.
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eloquent-builder.php',
            'eloquent-builder'
        );
    }

    private function registerBindings()
    {
        $this->app->bind(IFactory::class, FilterFactory::class);

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
