<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Eloquent Filter Settings
     |--------------------------------------------------------------------------
     |
     | Here you should specify default all you Eloquent Model Filters.
     |
     */
    'namespace' => 'App\\EloquentFilters\\',

    /*
     |--------------------------------------------------------------------------
     | Missing Filter Behavior
     |--------------------------------------------------------------------------
     |
     | When a filter key has no matching quick filter or filter class, the
     | package throws a FilterException by default. Set this to true to
     | silently ignore that key instead (as if it were never provided).
     |
     | This does not affect a filter class that exists but isn't a valid
     | Filter instance — that always throws, since it's a bug in your own
     | code rather than an unrecognized request key.
     |
     */
    'ignore_missing_filters' => false,
];
