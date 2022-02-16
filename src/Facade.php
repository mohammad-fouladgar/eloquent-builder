<?php

namespace Fouladgar\EloquentBuilder;

use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'eloquentbuilder';
    }
}
