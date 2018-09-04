<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFactory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/eloquent-builder.php' => config_path('eloquent-builder.php'),
        ], 'config');
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
            __DIR__.'/../config/eloquent-builder.php',
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
}
