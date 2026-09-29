# Upgrading

## v5 to v6

### PHP 8.3 required

The minimum PHP version is now `^8.3` (was `^8.2`). PHP 8.2 reached its own end of active support and no longer meets the floor required by this release's tooling (`laravel/pint` 1.32+ requires `^8.3`).

### Breaking changes

These are edge cases most apps won't hit, but each is a real change in observable behavior:

- **`EloquentBuilder::filters()` no longer accepts `null`.** The signature changed from `filters(array $filters = null)` to `filters(array $filters = [])`. Calling `->filters(null)` explicitly now throws a `TypeError`. Calling it with no arguments, or with a real array, is unaffected.
- **`EloquentBuilder::filter()` (the "push" method) precedence flipped.** It used to merge with `$this->filters += $filters`, where an *existing* value won on a key collision. It now uses `array_merge()`, where the *newest* pushed value wins:

  ```php
  // v5: gender ends up 'male' (the earlier value wins)
  // v6: gender ends up 'female' (the latest pushed value wins)
  EloquentBuilder::model(User::class)
      ->filters(['gender' => 'male'])
      ->filter(['gender' => 'female'])
      ->thenApply();
  ```

  If you relied on the old precedence for a filter key set more than once, swap the order of your `filters()`/`filter()` calls.

- **Filter authorization is scoped to filters actually run through the pipeline.** Previously, a global `Container::afterResolving()` hook called `authorize()` on *any* object resolved anywhere in the app that implemented `AuthorizeWhenResolved` — not just filters applied via `EloquentBuilder`. That hook is gone; authorization now only runs on filters resolved and applied by the pipeline. If something outside the pipeline was implementing `AuthorizeWhenResolved` to piggyback on that global hook, it's no longer authorized automatically.

### New in v6

All additive — safe to adopt incrementally, nothing above requires using them:

- **`QuickFilter`** — class-free filters: `exact()`, `partial()`, `scope()`, `callback()`, `trashed()`, `includes()`, `fields()`. See [Quick Filters](README.md#quick-filters).
- **`FilterGroup::or([...])`** — combine a set of filter keys with `OR` instead of the default `AND`. See [Filter Groups](README.md#filter-groups).
- **`EloquentBuilder::defaults()` / `ignoreValues()`** — default filter values and per-filter sentinel values to ignore. See [Defaults & Ignored Values](README.md#defaults--ignored-values).
- **`SortableTrait` custom sort resolvers** — map a `$sortable` key to a `Closure` instead of a plain column name. See [Sort filters](README.md#sort-filters).
- **`ignore_missing_filters` config toggle** — silently ignore an unrecognized filter key instead of throwing. See [Missing Filter Behavior](README.md#missing-filter-behavior).
- **`Builder::filter()` macro** — filter directly on an Eloquent `Builder`/model class, no facade needed: `User::filter($request->filter)->get()`. See [Fluent, Non-Facade Usage](README.md#fluent-non-facade-usage).
