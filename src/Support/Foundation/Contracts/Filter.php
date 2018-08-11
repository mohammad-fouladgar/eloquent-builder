<?php

namespace Fouladgar\EloquentBuilder\Support\Foundation\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    public function apply(Builder $builder, $value): Builder;
}
