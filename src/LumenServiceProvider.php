<?php

namespace Fouladgar\EloquentBuilder;

use Fouladgar\EloquentBuilder\Console\PublishCommand;

class LumenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        static $facadeRegistered = false;

        if (! $facadeRegistered) {
            $facadeRegistered = true;

            class_alias(Facade::class, 'EloquentBuilder');
        }

        parent::register();
    }

    protected function registerConsole(): void
    {
        parent::registerConsole();

        $this->commands(PublishCommand::class);
    }

    protected function bootPublishes(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'eloquent-builder');
    }
}
