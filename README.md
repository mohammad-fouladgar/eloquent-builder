# Provides a Eloquent query builder for Laravel or Lumen

[![Build Status](https://travis-ci.org/mohammad-fouladgar/eloquent-builder.svg?branch=develop)](https://travis-ci.org/mohammad-fouladgar/eloquent-builder)
[![Coverage Status](https://coveralls.io/repos/github/mohammad-fouladgar/eloquent-builder/badge.svg?branch=develop)](https://coveralls.io/github/mohammad-fouladgar/eloquent-builder?branch=develop)
[![StyleCI](https://github.styleci.io/repos/144369188/shield?branch=develop)](https://github.styleci.io/repos/144369188)
[![Latest Stable Version](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/v/stable)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
[![Total Downloads](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/downloads)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
[![License](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/license)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)

This package allows you to build eloquent queries, based on request parameters.
It greatly reduces the complexity of the queries and conditions, which will make your code cleaner.

## Basic Usage
Suppose we want to get the list of the users with the requested parameters as follows:
```php
//Get api/user/search?age_more_than=25&gender=male&has_published_post=true
[
    'age_more_than'  => '25',
    'gender'         => 'male',
    'has_published_post' => 'true',
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

        if ($request->has('has_published_post')) {
            $users->where(function ($query) use ($request) {
                $query->whereHas('posts', function ($query) use ($request) {
                    $query->where('is_published', $request->has_published_post);
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
use EloquentBuilder;
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

### Installation

- [Laravel](#laravel)
- [Lumen](#lumen)

### Laravel

You can install the package via composer:

```shell
composer require mohammad-fouladgar/eloquent-builder
```
> Laravel 5.5 uses Package Auto-Discovery, so you are not required to add ServiceProvider manually.

### Laravel <= 5.4.x
If you don't use Auto-Discovery, add the ServiceProvider to the providers array in ``config/app.php`` file
```php
'providers' => [
  /*
   * Package Service Providers...
   */
  Fouladgar\EloquentBuilder\ServiceProvider::class,
],

```
And add the **facade** to your ``config/app.php`` file
```php
/*
|--------------------------------------------------------------------------
| Class Aliases
|--------------------------------------------------------------------------
*/
'aliases' => [
    "EloquentBuilder" => Fouladgar\EloquentBuilder\Facade::class,
]
```
### Lumen

You can install the package via composer:

```shell
composer require mohammad-fouladgar/eloquent-builder
```
For Lumen, add the ``LumenServiceProvider`` to the ``bootstrap/app.php`` file

```php
/*
|--------------------------------------------------------------------------
| Register Service Providers...
|--------------------------------------------------------------------------
*/

$app->register(\Fouladgar\EloquentBuilder\LumenServiceProvider::class);
```
For using the facade you have to uncomment the line  ``$app->withFacades();`` in the ``bootstrap/app.php`` file

After uncommenting this line you have the ``EloquentBuilder`` facade enabled
```php
$app->withFacades();
```

Publish the configuration file 
```shell
php artisan eloquent-builder:publish
```
and  add the configuration to the ``bootstrap/app.php`` file 
```php
$app->configure('eloquent-builder');
...
$app->register(\Fouladgar\EloquentBuilder\LumenServiceProvider::class);
```
> **Important** : this needs to be before the registration of the service provider.

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



## Define a Filter
Writing a filter is simple. Define a class that implements the ``Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFilter`` interface. This interface requires you to implement one method: ``apply``. The ``apply`` method may add where constraints to the query as needed:
```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\IFilter as Filter;
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
## Ignore Filters on null value
Filter parameters are ignored if contain **empty** values or **null**.

Suppose we have a request something like:
```php
//Get api/user/search?name&gender=null&age_more_than=''&published_post=true

// Request result will be:
$filters = [
    'published_post'  => true
];
```
Only the **"published_post"** filter will be applied on your query.


## Work with existing queries
You may also want to work with existing queries. For example, consider the following code:
```php
<?php

namespace App\Http\Controllers;

use App\User;
use EloquentBuilder;
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
## Use as Dependency Injection
Suppose you want use the ``EloquentBuilder`` as ``DependencyInjection`` in a ``Repository``.

Let's have an example.We have a sample ``UserRepository`` as follows:
```php
<?php

namespace App\Repositories;

use App\User;
use Fouladgar\EloquentBuilder\EloquentBuilder;

class UserRepository extends BaseRepository
{
    
    public function __construct(EloquentBuilder $eloquentBuilder)
    {
        $this->eloquentBuilder = $eloquentBuilder;
        $this->makeModel();
    }

    public function makeModel()
    {
        return $this->setModel($this->model());
    }
    
    public function setModel($model)
    {
        $this->model = app()->make($model);

        return $this;
    }
    
    public function model()
    {
        return User::class;
    }
    
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    // other methods ...

    public function filters(array $filters)
    {
        $this->model = $this->eloquentBuilder->to($this->model(), $filters);

        return $this;
    }
}

```
The ``filters`` method applies the requested filters to the query by using ``EloquentBuilder`` injected.

### Injecting The Repository
Now,we can simply "type-hint" it in the constructor of our ``UserController``:
```php
<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{

    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function index(Request $request)
    {
        return $this->users->filters($request->all())->get();
    }
}
```
## Testing
```sh
composer test
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.
## Security

If you discover any security related issues, please email fouladgar.dev@gmail.com instead of using the issue tracker.

## License
Eloquent-Builder is released under the MIT License. See the bundled
 [LICENSE](https://github.com/mohammad-fouladgar/eloquent-builder/blob/master/LICENSE)
 file for details.

Built with :heart: for you.

