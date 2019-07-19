# Provides a Eloquent query builder for Laravel or Lumen

[![Build Status](https://travis-ci.org/mohammad-fouladgar/eloquent-builder.svg?branch=master)](https://travis-ci.org/mohammad-fouladgar/eloquent-builder)
[![Coverage Status](https://coveralls.io/repos/github/mohammad-fouladgar/eloquent-builder/badge.svg?branch=develop)](https://coveralls.io/github/mohammad-fouladgar/eloquent-builder?branch=develop)
[![StyleCI](https://github.styleci.io/repos/144369188/shield?branch=develop)](https://github.styleci.io/repos/144369188)
[![Latest Stable Version](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/v/stable)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
[![Total Downloads](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/downloads)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
[![License](https://poser.pugx.org/mohammad-fouladgar/eloquent-builder/license)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)

This package allows you to build eloquent queries, based on request parameters.
It greatly reduces the complexity of the queries and conditions, which will make your code cleaner.

## Basic Usage
Suppose you want to get the list of the users with the requested parameters as follows:
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

        // A User model may have an infinite numbers of Post(One-To-Many).
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
**But** after using the **EloquentBuilder**, the above code refactor as follows:

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
You just need to [define filter](#define%20a%20filter) for each parameter that you want to add to the query.
> **Tip**: It's recommended validates the incoming requests before sending to filters.

> **Tip**: It's recommended present filters inside a filter key in query string like this:  `user/search?filter[age_more_than]=25&filter[gender]=male`              
and then get them in this way: `$request->only('filter')`.

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
The default namespace for all filters is ``App\EloquentFilters`` with the base name of the Model. For example, the filters namespace will be `App\EloquentFilters\User` for the `User` model.

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
Writing a filter is simple. Define a class that `extends` the `Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter` abstract class. This class requires you to implement one method: ``apply``. The ``apply`` method may add where constraints to the query as needed.
Each filter class should be suffixed with the word `Filter`.

For example, take a look at the filter defined below:

```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class AgeMoreThanFilter extends Filter
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
> Tip: Also, you can easily use [local scopes](https://laravel.com/docs/5.8/eloquent#local-scopes) in your filter. Because they are instance of the query builder.

## Authorizing Filter
The filter class also contains an `authorize` method. Within this method, you may check if the authenticated user actually has the authority to apply a given filter. For example, you may determine if a user has a premium account, can apply the `StatusFilter` to get listing the online or offline people:

```php
/**
 * Determine if the user is authorized to make this filter.
 *
 * @return bool
 */
 public function authorize(): bool
 {
     if(auth()->user()->hasPremiumAccount()){
        return true;
     }

    return false
 }
```
By default, you do not need to implement the `authorize` method and the filter applies to your query builder.
If the `authorize` method returns `false`, a HTTP response with a 403 status code will automatically be returned.

## Ignore Filters on null value
Filter parameters are ignored if contain **empty** or **null** values.

Suppose you have a request something like this:

```php
//Get api/user/search?filter[name]&filter[gender]=null&filter[age_more_than]=''&filter[published_post]=true

EloquentBuilder::to(User::class,$request->only('filter'));

// filters result will be:
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

