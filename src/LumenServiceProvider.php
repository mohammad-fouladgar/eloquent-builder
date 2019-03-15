<?php

namespace Fouladgar\EloquentBuilder;

use Illuminate\Support\Facades\Facade;

class LumenServiceProvider extends ServiceProvider
{
    /**
     * Register facade.
     */
    public function register()
    {
        static $facadeRegistred = false;

        if (!$facadeRegistred) {
            $facadeRegistred = true;

            class_alias(\Fouladgar\EloquentBuilder\Facade::class, 'EloquentBuilder');
        }

        parent::register();
    }

    /**
     * Register the helper command to publish the config file.
     */
    protected function registerConsole()
    {
        parent::registerConsole();

        $this->commands(\Fouladgar\EloquentBuilder\Console\PublishCommand::class);
    }

    protected function bootPublishes()
    {
        $this->mergeConfigFrom($this->configPath(), 'eloquent-builder');
    }
}
