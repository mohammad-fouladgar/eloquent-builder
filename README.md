# Eloquent Builder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mohammad-fouladgar/eloquent-builder.svg)](https://packagist.org/packages/mohammad-fouladgar/eloquent-builder)
![Test Status](https://img.shields.io/github/actions/workflow/status/mohammad-fouladgar/eloquent-builder/run-tests.yml?label=tests)
![Code Style Status](https://img.shields.io/github/actions/workflow/status/mohammad-fouladgar/eloquent-builder/pint.yml?label=code%20style)
![Total Downloads](https://img.shields.io/packagist/dt/mohammad-fouladgar/eloquent-builder)

> If you are upgrading from v5 to v6, see [UPGRADE.md](UPGRADE.md).

![EloquentBuilder](./cover.jpg "EloquentBuilder")

Build clean, reusable, request-driven Eloquent queries in Laravel.

Eloquent Builder lets you map incoming request parameters to reusable Eloquent filters without filling your controllers with conditional query logic.

> **Current release:** v6
> **PHP:** 8.3+

## Why Eloquent Builder?

Filtering an Eloquent query can quickly become difficult to maintain when every request parameter requires its own conditional logic:

```php
$users = User::where('is_active', true);

if ($request->has('age_more_than')) {
    $users->where('age', '>', $request->age_more_than);
}

if ($request->has('gender')) {
    $users->where('gender', $request->gender);
}

if ($request->has('has_published_post')) {
    $users->whereHas('posts', function ($query) use ($request) {
        $query->where('is_published', $request->has_published_post);
    });
}
```

With Eloquent Builder, the filtering logic can live in dedicated filter classes or lightweight Quick Filters:

```php
return EloquentBuilder::model(User::class)
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

Your controllers stay focused on application flow while query-specific logic stays inside filters.

---

## Table of Contents

* [Installation](#installation)
* [Quick Start](#quick-start)
* [Filters](#filters)

    * [Defining a Filter](#defining-a-filter)
    * [Generating Filters with Artisan](#generating-filters-with-artisan)
    * [Using Filters](#using-filters)
    * [Fluent Usage](#fluent-usage)
* [Quick Filters](#quick-filters)

    * [Exact](#exact)
    * [Partial](#partial)
    * [Scope](#scope)
    * [Callback](#callback)
    * [Trashed](#trashed)
    * [Includes](#includes)
    * [Fields](#fields)
    * [Quick Filter Precedence](#quick-filter-precedence)
* [Filter Groups](#filter-groups)
* [Defaults and Ignored Values](#defaults-and-ignored-values)
* [Predefined Filters](#predefined-filters)

    * [Date Filters](#date-filters)
    * [Number Filters](#number-filters)
    * [Sort Filters](#sort-filters)
* [Authorization](#authorization)
* [Missing Filter Behavior](#missing-filter-behavior)
* [Ignoring Empty and Null Values](#ignoring-empty-and-null-values)
* [Custom Filter Namespaces](#custom-filter-namespaces)
* [Dependency Injection](#dependency-injection)
* [Choosing the Right Filter](#choosing-the-right-filter)
* [Upgrading](#upgrading)
* [Testing](#testing)
* [Contributing](#contributing)
* [Security](#security)
* [License](#license)

---

## Installation

Install the package via Composer:

```bash
composer require mohammad-fouladgar/eloquent-builder
```

**Requirements**

* PHP 8.3+
* Laravel Eloquent

> **Warning:** The `Lumen` framework is no longer supported.

### Upgrading from v5

If you are upgrading an existing application from v5, see [UPGRADE.md](UPGRADE.md) for breaking changes and new features introduced in v6.

---

## Quick Start

Suppose your API accepts these filters:

```http
GET /api/users?filter[age_more_than]=25&filter[gender]=male
```

Create an `AgeMoreThanFilter`:

```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class AgeMoreThanFilter extends Filter
{
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('age', '>', $value);
    }
}
```

Then apply the request filters:

```php
use App\Models\User;
use Fouladgar\EloquentBuilder\EloquentBuilder;

return EloquentBuilder::model(User::class)
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

The request key `age_more_than` is resolved to `AgeMoreThanFilter`.

For simple conditions, you can skip the filter class entirely and use a [Quick Filter](#quick-filters).

---

# Filters

## Defining a Filter

A filter is a class that extends:

```php
Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter
```

The class must implement `apply()`.

For example:

```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class AgeMoreThanFilter extends Filter
{
    public function apply(Builder $builder, mixed $value): Builder
    {
        return $builder->where('age', '>', $value);
    }
}
```

Filter classes should use the `Filter` suffix.

With the default namespace, filters for `User` live under:

```text
App\EloquentFilters\User
```

For example:

```text
app/
└── EloquentFilters/
    └── User/
        ├── AgeMoreThanFilter.php
        └── GenderFilter.php
```

> **Tip:** You can use Laravel local scopes inside your filter classes as well.

---

## Generating Filters with Artisan

Create a filter with:

```bash
php artisan eloquent-builder:make user age_more_than
```

You can generate multiple filters at once:

```bash
php artisan eloquent-builder:make user age_more_than gender
```

---

## Using Filters

### Model class

```php
$users = EloquentBuilder::model(User::class)
    ->filters(request()->filter)
    ->thenApply()
    ->get();
```

### Existing query

You can start with an existing Eloquent query:

```php
$query = User::where('is_active', true);

$users = EloquentBuilder::model($query)
    ->filters(request()->filter)
    ->thenApply()
    ->where('city', 'london')
    ->get();
```

### Programmatically adding filters

You can also push filter values directly:

```php
$users = EloquentBuilder::model(new User())
    ->filters(request()->filter)
    ->filter(['age_more_than' => '30'])
    ->filter(['gender' => 'female'])
    ->thenApply()
    ->get();
```

When the same filter key is pushed more than once, the latest value takes precedence in v6.

---

## Fluent Usage

For the common `filters()` use case, you can use the `filter()` macro directly on an Eloquent model or query:

```php
$users = User::filter(request()->filter)->get();
```

It also works with an existing query:

```php
$users = User::where('is_active', true)
    ->filter(request()->filter)
    ->get();
```

This is the simplest way to apply standard filters without using `EloquentBuilder::model()`.

For advanced features such as:

* `quickFilters()`
* `filterGroups()`
* `defaults()`
* `ignoreValues()`

use the full `EloquentBuilder` API.

> **Tip:** It is recommended to put filter parameters under a `filter` request key:

```http
/api/users?filter[age_more_than]=25&filter[gender]=male
```

Then access them through:

```php
$request->filter
```

---

# Quick Filters

Quick Filters are designed for simple filtering logic where creating a dedicated filter class would add unnecessary boilerplate.

Import:

```php
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\QuickFilter;
```

Then define filters inline:

```php
EloquentBuilder::model(User::class)
    ->quickFilters([
        QuickFilter::exact('gender'),
        QuickFilter::partial('name'),
        QuickFilter::scope('online'),
        QuickFilter::callback(
            'has_posts',
            fn (Builder $builder, mixed $value) =>
                $builder->whereHas('posts')
        ),
        QuickFilter::trashed(),
    ])
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

### Quick Filter Reference

| Quick Filter | Purpose                           |
| ------------ | --------------------------------- |
| `exact()`    | Exact `=` comparison              |
| `partial()`  | `LIKE %value%` comparison         |
| `scope()`    | Apply an Eloquent local scope     |
| `callback()` | Apply custom query logic          |
| `trashed()`  | Control soft-deleted records      |
| `includes()` | Eager-load allowed relationships  |
| `fields()`   | Select allowed root-model columns |

---

## Exact

Use `exact()` for a simple equality condition.

```php
QuickFilter::exact('status')
```

Request:

```http
GET /api/users?filter[status]=active
```

Equivalent query:

```php
$query->where('status', 'active');
```

You can map the request key to a different column:

```php
QuickFilter::exact('user_status', 'status')
```

Request:

```http
GET /api/users?filter[user_status]=active
```

This applies:

```php
$query->where('status', 'active');
```

---

## Partial

Use `partial()` for a `LIKE` query:

```php
QuickFilter::partial('name')
```

Request:

```http
GET /api/users?filter[name]=john
```

Equivalent query:

```php
$query->where('name', 'LIKE', '%john%');
```

You can also map the request key to a different column:

```php
QuickFilter::partial('search', 'name')
```

---

## Scope

Use `scope()` when the filtering logic already exists as an Eloquent local scope.

For example:

```php
public function scopeOnline(Builder $query, mixed $value): Builder
{
    return $query->where('is_online', $value);
}
```

Register the scope:

```php
QuickFilter::scope('online')
```

Request:

```http
GET /api/users?filter[online]=1
```

The corresponding local scope is invoked with the filter value.

If the request key and scope name are different:

```php
QuickFilter::scope('active', 'online')
```

This maps:

```text
filter[active]
     ↓
scopeOnline()
```

---

## Callback

Use `callback()` when you need custom query logic but do not want to create a dedicated filter class.

```php
QuickFilter::callback(
    'has_posts',
    fn (Builder $builder, mixed $value) =>
        $builder->whereHas('posts')
)
```

Request:

```http
GET /api/users?filter[has_posts]=1
```

The callback receives:

```php
Builder $builder
mixed $value
```

and can modify the query.

For complex or reusable filtering logic, prefer a class-based filter.

---

## Trashed

`trashed()` provides filtering for models using Laravel's `SoftDeletes` trait.

```php
QuickFilter::trashed()
```

The default request key is:

```text
trashed
```

### Include trashed records

```http
GET /api/posts?filter[trashed]=with
```

Equivalent to:

```php
$query->withTrashed();
```

### Only trashed records

```http
GET /api/posts?filter[trashed]=only
```

Equivalent to:

```php
$query->onlyTrashed();
```

Any other value uses the default behavior and excludes trashed records.

You can customize the request key:

```php
QuickFilter::trashed('deleted')
```

> **Warning:** `QuickFilter::trashed()` requires Laravel's `SoftDeletes` trait. Using `with` or `only` with a model that does not support soft deletes results in a `BadMethodCallException`.

---

## Includes

`includes()` lets clients request eager-loaded relationships while restricting them to an explicit whitelist.

```php
QuickFilter::includes('include', [
    'posts',
    'posts.comments',
    'profile',
])
```

Request:

```http
GET /api/users?filter[include]=posts,posts.comments
```

The query will eager-load the requested allowed relationships.

The request may also provide an array:

```php
[
    'posts',
    'posts.comments',
]
```

### Whitelisting

Only relations present in the `$allowed` list are accepted.

For example:

```php
QuickFilter::includes('include', [
    'posts.user',
])
```

allows:

```text
posts.user
```

but does not automatically allow:

```text
posts
```

Anything not present in the whitelist is ignored.

---

## Fields

`fields()` lets clients request a limited set of columns from the root model.

```php
QuickFilter::fields('fields', [
    'id',
    'name',
    'email',
])
```

Request:

```http
GET /api/users?filter[fields]=name,email
```

The selected columns will include:

```text
id
name
email
```

The primary key is always selected, even if it was not explicitly requested.

If none of the requested fields are allowed, only the primary key is selected.

### Root model only

`fields()` currently applies only to the root model.

It does not provide relation-specific fieldsets such as:

```text
fields[posts]=title
```

Use a class-based filter or `QuickFilter::callback()` for more advanced selection logic.

### Important: `fields()` uses `select()`

`fields()` calls Eloquent's `select()` method.

That means it replaces the query's existing column list.

Avoid combining it with another filter that also calls `select()`.

If another filter needs to add columns after `fields()` has been applied, use `addSelect()` where appropriate.

---

## Quick Filter Precedence

Quick Filters take precedence over class-based filters when they use the same request key.

For example:

```php
QuickFilter::exact('status')
```

takes precedence over a class-based `StatusFilter` for the same `status` key.

> **Note:** Quick Filters do not support the `authorize()` method available to class-based filters. Use a class-based filter when filter-level authorization is required.

---

# Filter Groups

By default, filters are combined with `AND`.

For example:

```http
GET /api/users?filter[status]=online&filter[is_featured]=true
```

normally produces:

```sql
WHERE status = 'online'
AND is_featured = true
```

Use `FilterGroup::or()` to combine a group of filter keys with `OR`:

```php
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterGroup;

EloquentBuilder::model(User::class)
    ->filterGroups([
        FilterGroup::or([
            'status',
            'is_featured',
        ]),
    ])
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

The same request now produces:

```sql
WHERE (
    status = 'online'
    OR is_featured = true
)
```

Filter groups can contain:

* class-based filters
* Quick Filters
* a combination of both

Only filter keys that are present in the request are applied.

If only one member is present, it behaves like a normal filter.

If none are present, the group is skipped.

### Filters inside groups

Filter groups are intended for filters that add normal `where` constraints.

Good candidates include:

```text
QuickFilter::exact()
QuickFilter::partial()
QuickFilter::scope()
QuickFilter::callback()
```

Avoid putting filters that change the overall query structure inside an OR group, especially filters that:

```text
withoutGlobalScope()
orderBy()
with()
```

or otherwise modify query state outside the nested `where` condition.

`QuickFilter::trashed()` should also be used outside filter groups.

---

# Defaults and Ignored Values

## Defaults

Use `defaults()` when a filter should receive a value if none is provided.

```php
EloquentBuilder::model(User::class)
    ->quickFilters([
        QuickFilter::exact('status'),
    ])
    ->defaults([
        'status' => 'online',
    ])
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

If the request does not contain `filter[status]`, the default value is used.

An explicitly provided value takes precedence over the default.

---

## Ignored Values

Use `ignoreValues()` when a specific incoming value should be treated as if the filter was not provided.

This is useful for UI sentinel values such as:

```text
filter[status]=all
```

Example:

```php
EloquentBuilder::model(User::class)
    ->quickFilters([
        QuickFilter::exact('status'),
    ])
    ->ignoreValues([
        'status' => ['all'],
    ])
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

The value `all` is ignored.

You can combine ignored values with defaults:

```php
EloquentBuilder::model(User::class)
    ->quickFilters([
        QuickFilter::exact('status'),
    ])
    ->ignoreValues([
        'status' => ['all'],
    ])
    ->defaults([
        'status' => 'online',
    ])
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

Now:

```text
filter[status]=all
        │
        ▼
     ignored
        │
        ▼
default = online
```

Values are compared as strings, so:

```php
ignoreValues([
    'id' => [0],
])
```

also matches an incoming `'0'`.

> **Note:** `defaults()` only applies when the filter has no value after processing. A non-empty value that happens to contain no allowed items does not automatically trigger the default.

---

# Predefined Filters

Eloquent Builder provides reusable traits for common filtering requirements:

* Date filtering
* Number filtering
* Sorting

---

## Date Filters

Use `FiltersDatesTrait` for date-based filters.

Supported conventions:

```text
between:date1,date2
before:date
before_or_equal:date
after:date
after_or_equal:date
same:date
equals:date
```

### Examples

```http
GET /api/users?birth_date=before:2018-01-01
```

### Between

These forms can be used for a range:

```http
birth_date=between:2018-01-01,2022-01-01
```

```http
birth_date=2018-01-01,2022-01-01
```

```http
birth_date[]=2018-01-01&birth_date[]=2022-01-01
```

### Equals

These forms represent equality:

```http
birth_date=equals:2018-01-01
```

```http
birth_date=same:2018-01-01
```

```http
birth_date=2018-01-01
```

### Defining a date filter

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
        return $this->filterDate(
            $builder,
            $value,
            'birth_date'
        );
    }
}
```

---

## Number Filters

Use `FiltersNumbersTrait` for numeric filters.

Supported conventions:

```text
between:number1,number2
gt:number
gte:number
lt:number
lte:number
equals:number
```

### Examples

```http
GET /api/users?score=gte:500
```

### Between

```http
score=between:100,1010
```

or:

```http
score=100,1010
```

or:

```http
score[]=100&score[]=1010
```

### Equals

```http
score=equals:2222
```

or:

```http
score=2222
```

### Defining a number filter

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
        return $this->filterNumber(
            $builder,
            $value,
            'score'
        );
    }
}
```

---

## Sort Filters

Use `SortableTrait` to expose a controlled set of sortable columns.

For example:

```http
GET /api/users?sort_by[birth_date]=desc&sort_by[id]=asc
```

You can also use:

```http
GET /api/users?sort_by[]=birth_date:desc&sort_by[]=id:asc
```

If no direction is specified, `asc` is used:

```http
GET /api/users?sort_by[]=birth_date
```

### Defining a sort filter

```php
<?php

namespace App\EloquentFilters\User;

use Fouladgar\EloquentBuilder\Concerns\SortableTrait;
use Fouladgar\EloquentBuilder\Support\Foundation\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

class SortByFilter extends Filter
{
    use SortableTrait;

    protected array $sortable = [
        'birth_date',
        'score',
    ];

    public function apply(Builder $builder, mixed $value): Builder
    {
        return $this->applySort($builder, $value);
    }
}
```

Only columns listed in `$sortable` can be requested by the client.

### Custom sort resolvers

A sortable key can map to a custom closure instead of a direct database column.

For example, sorting by the number of posts:

```php
protected array $sortable = [
    'birth_date',
    'score',

    'posts_count' => function (
        Builder $builder,
        string $direction
    ): Builder {
        return $builder
            ->withCount('posts')
            ->orderBy('posts_count', $direction);
    },
];
```

Request:

```http
GET /api/users?sort_by[posts_count]=desc
```

The resolver receives the builder and sort direction and should mutate the provided builder.

> **Note:** The resolver's return value is ignored. Returning a different `Builder` instance does not replace the current query.

### Default sorting

Combine `SortableTrait` with `defaults()` to apply a default sort:

```php
EloquentBuilder::model(User::class)
    ->defaults([
        'sort_by' => [
            'created_at' => 'desc',
        ],
    ])
    ->filters($request->filter)
    ->thenApply()
    ->get();
```

---

# Authorization

Class-based filters can implement `authorize()` when filter-level authorization is required.

For example:

```php
public function authorize(): bool
{
    return auth()->user()->hasPremiumAccount();
}
```

If `authorize()` returns `false`, the filter is rejected through Laravel's authorization mechanism.

You do not need to implement `authorize()` when a filter does not require authorization.

> **Note:** Authorization is performed for filters that are resolved and applied through the Eloquent Builder pipeline. Quick Filters do not provide an `authorize()` method.

---

# Missing Filter Behavior

By default, an incoming filter key without a matching Quick Filter or filter class throws a `FilterException`.

You can change this behavior in the published configuration:

```php
'ignore_missing_filters' => true,
```

With this option enabled, unrecognized filter keys are silently ignored.

This can be useful when a request payload contains additional parameters that are not intended to be filters.

> **Note:** This only affects unrecognized keys. If a matching filter class exists but is not a valid `Filter` instance, that is still treated as an error.

---

# Ignoring Empty and Null Values

Filter parameters with empty or null values are ignored.

For example:

```text
filter[name]
filter[gender]=null
filter[age_more_than]=
filter[published_post]=true
```

Only the filter with a meaningful value is applied.

---

# Custom Filter Namespaces

The default filter namespace is:

```text
App\EloquentFilters\
```

You can customize it by publishing the configuration:

```bash
php artisan vendor:publish \
    --provider="Fouladgar\EloquentBuilder\ServiceProvider" \
    --tag="config"
```

Then configure:

```php
return [
    'namespace' => 'App\\EloquentFilters\\',
];
```

### Per-domain namespaces

For applications using domain-based structures, you can set the namespace for a specific query:

```php
$stores = EloquentBuilder::model(\Domains\Entities\Store::class)
    ->filters($request->all())
    ->setFilterNamespace('Domains\\Store\\Filters')
    ->thenApply()
    ->get();
```

For example:

```text
Domains/
├── Store/
│   └── src/
│       └── Filters/
│           └── StoreFilter.php
│
└── User/
    └── src/
        └── Filters/
            └── UserFilter.php
```

> **Note:** When `setFilterNamespace()` is used, the default namespace and configured namespace are ignored for that builder instance.

---

# Dependency Injection

`EloquentBuilder` can be injected into a controller or another service.

For example:

```php
<?php

namespace App\Controllers;

use App\Models\User;
use Fouladgar\EloquentBuilder\EloquentBuilder;
use Illuminate\Http\Request;

class UserController
{
    public function index(
        Request $request,
        User $user,
        EloquentBuilder $builder
    ) {
        $users = $user->newQuery()
            ->where('is_active', true);

        $builder
            ->model($users)
            ->filters($request->filter)
            ->thenApply();

        return $users->get();
    }
}
```

---

# Choosing the Right Filter

Use this as a quick decision guide:

| Requirement                     | Recommended API           |
| ------------------------------- | ------------------------- |
| Simple `=` comparison           | `QuickFilter::exact()`    |
| Simple `LIKE` search            | `QuickFilter::partial()`  |
| Existing Eloquent local scope   | `QuickFilter::scope()`    |
| One-off custom query logic      | `QuickFilter::callback()` |
| Soft-delete filtering           | `QuickFilter::trashed()`  |
| Client-controlled eager loading | `QuickFilter::includes()` |
| Client-controlled root columns  | `QuickFilter::fields()`   |
| Reusable or complex logic       | Class-based `Filter`      |
| Filter-level authorization      | Class-based `Filter`      |
| OR conditions                   | `FilterGroup::or()`       |
| Date comparisons                | `FiltersDatesTrait`       |
| Numeric comparisons             | `FiltersNumbersTrait`     |
| Controlled sorting              | `SortableTrait`           |

---

# Testing

Run the test suite with:

```bash
composer test
```
---

# Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

# Security

If you discover a security-related issue, please email:

[fouladgar.dev@gmail.com](mailto:fouladgar.dev@gmail.com)

Please do not use the public issue tracker for security vulnerabilities.

---

# License

Eloquent Builder is released under the MIT License.

See the [LICENSE](LICENSE) file for details.

---

Built with ❤️ for you.
