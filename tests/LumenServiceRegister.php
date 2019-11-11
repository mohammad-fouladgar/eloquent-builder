<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\LumenServiceProvider;

trait LumenServiceRegister
{
    /**
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LumenServiceProvider::class];
    }
}
