# Provides a Eloquent query builder for Laravel

![alt text](./cover.jpg "EloquentBuilder")

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mohammad-fouladgar/eloquent-builder.svg)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
![Test Status](https://img.shields.io/github/actions/workflow/status/mohammad-fouladgar/eloquent-builder/run-tests.yml?label=tests)
![Code Style Status](https://img.shields.io/github/actions/workflow/status/mohammad-fouladgar/eloquent-builder/php-cs-fixer.yml?label=code%20style)
![Total Downloads](https://img.shields.io/packagist/dt/mohammad-fouladgar/eloquent-builder)

This package allows you to build eloquent queries, based on incoming request parameters. It greatly reduces the complexity of the
queries and conditions, which will make your code clean and maintainable.

## Version Compatibility

| Laravel          | EloquentBuilder   |
|:-----------------|:------------------|
| 11.0.x to 12.0.x | 5.x.x             |
| 10.0.x           | 4.2.x             |
| 9.0.x            | 4.0.x             |
| 6.0.x to 8.0.x   | 3.0.x             |
| 5.0.x            | 2.2.2             |

## Basic Usage

Suppose you want to get the list of the users with the requested parameters as follows:

```php
//Get api/user/search?age_more_than=25&gender=male&has_published_post=true
[
    'age_more_than'  => '25',
    'gender'         => 'male',
    'has_published_post' => true,
]
```

In a __common__ implementation, following code will be expected:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
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

**But** after using the **EloquentBuilder**, the above code will turns into this:

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
        return EloquentBuilder::model(User::class)
            ->filters($request->all())
            ->thenApply()
            ->get();
    }
}
```

You just need to [define a filter](#define-a-filter) for each parameter that you want to add to the query.

### Installation
You can install the package via composer:

```shell
composer require mohammad-fouladgar/eloquent-builder
```

>  **Warning:** The `Lumen` framework is no longer supported!

### Filters Namespace

The default namespace for all filters is ``App\EloquentFilters`` with the base name of the Model. For example, the
filters' namespace will be `App\EloquentFilters\User` for the `User` model:

```
├── app
├── Console
│   └── Kernel.php
├── EloquentFilters
│   └── User
│       ├── AgeMoreThanFilter.php
│       └── GenderFilter.php
└── Exceptions
    └── Handler.php
```

#### Customize via Config file

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
     | Here you should specify default all you Eloquent Model Filters.
     |
     */
    'namespace' => 'App\\EloquentFilters\\',
];
```

## Defining a Filter

Writing a filter is simple. Define a class that `extends`
the `Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter` abstract class. This class requires you to implement
one method: ``apply``. The ``apply`` method may add where constraints to the query as needed. Each filter class should
be suffixed with the word `Filter`.

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
     */
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('age', '>', $value);
    }
}
```

> **Tip**: Also, you can easily use [local scopes](https://laravel.com/docs/5.8/eloquent#local-scopes) in your filter. Because, they are instancing of the query builder.

### Define filter[s] by artisan command

If you want to create a filter easily, you can use `eloquent-builder:make` artisan command. This command will accept at
least two arguments which are `Model` and `Filter`:

```
php artisan eloquent-builder:make user age_more_than
```

There is also a possibility of creating multiple filters at the same time. To achieve this goal, you should pass
multiple names to `Filter` argument:

```
php artisan eloquent-builder:make user age_more_than gender
```

## Use a filter

You can use filters in multiple approaches:

```php
<?php

// Use by a model class name
$users = EloquentBuilder::model(\App\Models\User::class)->filters(request()->all())->thenApply()->get();

// Use by existing query
$query = \App\Models\User::where('is_active', true);

$users = EloquentBuilder::model($query)
        ->filters(request()->all())
        ->thenApply()
        ->where('city', 'london')
        ->get();

// Use by instance of a model and push filter
$users = EloquentBuilder::model(new \App\Models\User())
        ->filters(request()->filter)
        ->filter(['age_more_than' => '30'])
        ->filter(['gender' => 'female'])
        ->thenApply()
        ->get();
```

> **Tip**: It's recommended to put your query params inside a filter key as below:

 ```
 user/search?filter[age_more_than]=25&filter[gender]=male
 ```

And then use them this way: `request()->filter`.

## Use Predefined Filters
This package provides several predefined filters using string conventions, so you can use them in your filter classes
easily.

> **Tip**: All value(s) in string conventions will be validated according to the used filter.

### Date filters
Date filtering is one of the most commonly used filters that you may use in your filters by following these
conventions: `between:date1,date2`,`before:date`, `before_or_equal:date`, `after:date`, `after_or_equal:date`
, `same:date`, and `equals:date`.

**Examples:**

```shell
api/user/search?birth_date=before:2018-01-01

# These are similar between convention:
api/user/search?birth_date=between:2018-01-01,2022-01-01
api/article/search?birth_date=2018-01-01,2022-01-01 
api/article/search?birth_date[]=2018-01-01&birth_date[]=2022-01-01 

# These are similar equals convention:
api/user/search?birth_date=equals:2018-01-01
api/user/search?birth_date=same:2018-01-01
api/user/search?birth_date=2018-01-01
```

All you need is to define a filter and use the `Fouladgar\EloquentBuilder\Concerns\FiltersDatesTrait` trait. For
example:

```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Concerns\FiltersDatesTrait;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class BirthDateFilter extends Filter
{
    use FiltersDatesTrait;

    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->filterDate($builder, $value, 'birth_date');
    }
}
```

### Number filters
Another predefined filters is Number filters that you can use in your filters. For example, it would be useful for price
filter,score filters, and any numeric filters. You can follow these numeric conventions:  
`between:number1,number2`,`gt:number`,`gte:number`,`lt:number`,`lte:number`, and `equals:number`.

**Examples:**

```shell
api/user/search?score=gte:500

# These are similar between convention:
api/user/search?score=between:100,1010
api/article/search?score=100,1010
api/article/search?score[]=100&score[]=1010

# These are similar equals convention:
api/user/search?score=equals:2222
api/user/search?score=2222
```

For example, make a `ScoreFilter` and use `Fouladgar\EloquentBuilder\Concerns\FiltersNumbersTrait` trait as below:

```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Concerns\FiltersNumbersTrait;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class ScoreFilter extends Filter
{
    use FiltersNumbersTrait;

    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->filterNumber($builder, $value, 'score');
    }
}
```

### Sort filters
You may want to sort your query filter. There are some usage to make it:

**Examples:**
```shell
api/user/search?sort_by[birth_date]=desc&sort_by[id]=asc

api/user/search?sort_by[]=birth_date:desc&sort_by[]=id:asc

# The default direction is `asc`:
api/user/search?sort_by[]=birth_date
```
For example make a `SortByFilter` and use the `Fouladgar\EloquentBuilder\Concerns\SortableTrait` trait. 
```php
<?php

namespace App\EloquentBuilders\User;

use Fouladgar\EloquentBuilder\Concerns\SortableTrait;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class SortByFilter extends Filter
{
    use SortableTrait;

    protected array $sortable = [
        'birth_date', 'score',
    ];

    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->applySort($builder, $value);
    }
}
```
> **Tip**: The sortable column(s) should be specified by `$sortable` attribute.

## Authorizing Filter
The filter class also contains an `authorize` method. Within this method, you may check if the authenticated user
actually has the authority to apply a given filter. For example, you may determine if a user has a premium account, can
apply the `StatusFilter` to get listing the online or offline people:

```php
/**
 * Determine if the user is authorized to make this filter.
 */
 public function authorize(): bool
 {
     if(auth()->user()->hasPremiumAccount()){
        return true;
     }

    return false;
 }
```

By default, you do not need to implement the `authorize` method and the filter applies to your query builder. If
the `authorize` method returns `false`, a HTTP response with a 403 status code will automatically be returned.

## Ignore Filters on null value

Filter parameters are ignored if contain **empty** or **null** values.

Suppose you have a request something like this:

```php
//Get api/user/search?filter[name]&filter[gender]=null&filter[age_more_than]=''&filter[published_post]=true

EloquentBuilder::model(User::class)->filters($request->filter)->thenApply();

// filters result will be:
$filters = [
    'published_post' => true
];
```

Only the **"published_post"** filter will be applied on your query.

### Customize per domain/module

When you have a laravel project with custom directory structure, you might need to have multiple filters in multiple
directories. For this purpose, you can use `setFilterNamespace()` method and pass the desired namespace to it.

For example, let's assume you have a project which implement a domain based structure:

```
.
├── app
├── bootstrap
├── config
├── database
├── Domains
│   ├── Store
│   │   ├── database
│   │   │   └── migrations
│   │   ├── src
│   │       ├── Filters // We put our Store domain filters here!
│   │       │   └── StoreFilter.php
│   │       ├── Entities
│   │       ├── Http
│   │          └── Controllers
│   │       ├── routes
│   │       └── Services
│   ├── User
│   │   ├── database
│   │   │   └── migrations
│   │   ├── src
│   │       ├── Filters // We put our User domain filters here!
│   │       │   └── UserFilter.php
│   │       ├── Entities
│   │       ├── Http
│   │          └── Controllers
│   │       ├── routes
│   │       └── Services
...
```

In the above example, each domain has its own filters directory. So we can set and use filters custom namespace as
following:

```php
$stores = EloquentBuilder::model(\Domains\Entities\Store::class)
            ->filters($request->all())
            ->setFilterNamespace('Domains\\Store\\Filters')
            ->thenApply()
            ->get();
```

> **Note**: When using `setFilterNamespace()`, default namespace and config file will be ignored.

## Use as Dependency Injection

You may need to use the `EloquentBuilder` as `DependencyInjection` in a `construct` or a `function` method.

Suppose you have an `UserController` and you want get a list of the users with applying some filters on them:

```php
<?php

namespace App\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Fouladgar\EloquentBuilder\EloquentBuilder as Builder;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Illuminate\Http\Request;

class UserController
{
    public function index(Request $request, User $user, Builder $builder)
    {
        $users = $user->newQuery()->where('is_active', true);
        try {
            $builder->model($users)
                    ->filters($request->filter)
                    ->thenApply();
        } catch (FilterException $filterException) {
            //...
        }

        return UserResource::collection($users->get());
    }
}
```

That's it.

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

