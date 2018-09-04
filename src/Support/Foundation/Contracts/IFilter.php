<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface IFilter
{
    public function apply(Builder $builder, $value): Builder;
}
