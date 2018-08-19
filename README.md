# Provides a Eloquent query builder for Laravel 5

[![Build Status](https://travis-ci.org/mohammad-fouladgar/eloquent-builder.svg?branch=develop)](https://travis-ci.org/mohammad-fouladgar/eloquent-builder)
[![Coverage Status](https://coveralls.io/repos/github/mohammad-fouladgar/eloquent-builder/badge.svg?branch=develop)](https://coveralls.io/github/mohammad-fouladgar/eloquent-builder?branch=develop)
[![StyleCI](https://github.styleci.io/repos/144369188/shield?branch=develop)](https://github.styleci.io/repos/144369188)
[![Latest Stable Version](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/v/stable)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
[![Total Downloads](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/downloads)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
[![License](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/license)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)

This package allows you to build eloquent queries, based on request parameters.
It greatly reduces the complexity of the queries and conditions, which will make your code cleaner.

### Installation
```shell
composer require mohammad-fouladgar/eloquent-builder
```
Laravel 5.5 uses Package Auto-Discovery, so you are not required to add ServiceProvider manually.

### Laravel 5.5+
If you don't use Auto-Discovery, add the ServiceProvider to the providers array in config/app.php
```php
Fouladgar\EloquentBuilder\ServiceProvider::class,
```

### Default Filters Namespace
The default namespace for all filters is  ``App\EloquentFilters\``  with the base name of the Model.

For example:

Suppose we have a **User** model with an **AgeMoreThan** filter.As a result, the namespace filter must be as follows:

``
App\EloquentFilters\User\AgeMoreThanFilter
``
#### With Config file
You can optionally publish the config file with:
```sh
php artisan vendor:publish --provider="Fouladgar\EloquentBuilder\ServiceProvider" --tag="config"
```
And set the namespace for your model filters which will reside in:
```php
return [
    /*
     |--------------------------------------------------------------------------
     | Eloquent Filter Settings
     |--------------------------------------------------------------------------
     |
     | This is the namespace all you Eloquent Model Filters will reside
     |
     */
    'namespace' => 'App\\EloquentFilters\\',
];
```

## Usage
Suppose we want to get the users list with the  requested parameters as below:
```php
[
    'age_more_than'  => '25',
    'gender'         => 'female',
    'published_post' => 'true',
]
```
In the __legacy__ code the method written below was followed:
```php
<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('is_active', true);

        if ($request->has('age_more_than')) {
            $users->where('age', '>', $request->age_more_than);
        }

        if ($request->has('gender')) {
            $users->where('gender', $request->gender);
        }

        if ($request->has('published_post')) {
            $users->where(function ($query) use ($request) {
                $query->whereHas('posts', function ($query) use ($request) {
                    $query->where('is_published', $request->published_post);
                });
            });
        }

        return $users->get();
    }
}
```
**But** the new method with **EloquentBuilder** follows the steps below:
```php
<?php

namespace App\Http\Controllers;

use App\User;
use Fouladgar\EloquentBuilder\EloquentBuilder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = EloquentBuilder::to(User::class, $request->all());

        return $users->get();
    }
}
```

> **Note**: It's recommended validates the incoming requests before sending to filters.

## Define a Filter
Writing a filter is simple. Define a class that implements the ``Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter`` interface. This interface requires you to implement one method: ``apply``. The ``apply`` method may add where constraints to the query as needed:
```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class AgeMoreThanFilter implements Filter
{
    /**
     * Apply the age condition to the query.
     *
     * @param Builder $builder
     * @param mixed   $value
     *
     * @return Builder
     */
    public function apply(Builder $builder, $value): Builder
    {
        return $builder->where('age', '>', $value);
    }
}
```

## Work with existing queries
You may also want to work with existing queries. For example, consider the following code:
```php
<?php

namespace App\Http\Controllers;

use App\User;
use Fouladgar\EloquentBuilder\EloquentBuilder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_active', true);
        $users = EloquentBuilder::to($query, $request->all())
            ->where('city', 'london')
            ->paginate();

        return $users;
    }
}
```

## Testing
```sh
composer test
```

## License
Eloquent-Builder is released under the MIT License. See the bundled
 [LICENSE](https://github.com/mohammad-fouladgar/eloquent-builder/blob/master/LICENSE)
 file for details.

Built with :heart: for you.

