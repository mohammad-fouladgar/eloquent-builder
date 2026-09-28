<?php

declare(strict_types=1);

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\QuickFilter;
use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class QuickFilterTest extends TestCase
{
    /** @test */
    public function it_throws_when_the_trashed_quick_filter_is_used_on_a_non_soft_deletable_model(): void
    {
        $this->expectException(\BadMethodCallException::class);

        User::factory()->create();

        $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::trashed()])
            ->filters(['trashed' => 'with'])
            ->thenApply()
            ->get();
    }

    /** @test */
    public function it_can_filter_using_an_exact_quick_filter(): void
    {
        User::factory()->create(['gender' => 'male']);
        User::factory()->create(['gender' => 'female']);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::exact('gender')])
            ->filters(['gender' => 'female'])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('female', $users->first()->gender);
    }

    /** @test */
    public function it_can_filter_using_a_partial_quick_filter(): void
    {
        User::factory()->create(['name' => 'Mohammad']);
        User::factory()->create(['name' => 'Ali']);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::partial('name')])
            ->filters(['name' => 'oha'])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('Mohammad', $users->first()->name);
    }

    /** @test */
    public function it_can_filter_using_a_scope_quick_filter(): void
    {
        User::factory()->create(['status' => 'online']);
        User::factory()->create(['status' => 'offline']);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::scope('online')])
            ->filters(['online' => true])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $users->count());
    }

    /** @test */
    public function it_can_filter_using_a_callback_quick_filter(): void
    {
        User::factory(2)
            ->create()
            ->each(function ($user) {
                $user->posts()->save(Post::factory()->make(['is_published' => true]));
            });

        User::factory()->create();

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([
                QuickFilter::callback('has_published_post', fn ($builder, $value) => $value
                    ? $builder->whereHas('posts', fn ($query) => $query->where('is_published', true))
                    : $builder),
            ])
            ->filters(['has_published_post' => true])
            ->thenApply()
            ->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test */
    public function it_can_include_trashed_records_with_the_trashed_quick_filter(): void
    {
        Post::factory()->create(['user_id' => 1, 'title' => 'active']);
        Post::factory()->create(['user_id' => 1, 'title' => 'deleted'])->delete();

        $posts = $this->eloquentBuilder
            ->model(Post::class)
            ->quickFilters([QuickFilter::trashed()])
            ->filters(['trashed' => 'with'])
            ->thenApply()
            ->get();

        $this->assertEquals(2, $posts->count());
    }

    /** @test */
    public function it_can_get_only_trashed_records_with_the_trashed_quick_filter(): void
    {
        Post::factory()->create(['user_id' => 1, 'title' => 'active']);
        Post::factory()->create(['user_id' => 1, 'title' => 'deleted'])->delete();

        $posts = $this->eloquentBuilder
            ->model(Post::class)
            ->quickFilters([QuickFilter::trashed()])
            ->filters(['trashed' => 'only'])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $posts->count());
        $this->assertEquals('deleted', $posts->first()->title);
    }

    /** @test */
    public function it_excludes_trashed_records_by_default_with_the_trashed_quick_filter(): void
    {
        Post::factory()->create(['user_id' => 1, 'title' => 'active']);
        Post::factory()->create(['user_id' => 1, 'title' => 'deleted'])->delete();

        $posts = $this->eloquentBuilder
            ->model(Post::class)
            ->quickFilters([QuickFilter::trashed()])
            ->filters(['trashed' => 'anything-else'])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $posts->count());
        $this->assertEquals('active', $posts->first()->title);
    }

    /** @test */
    public function quick_filters_take_precedence_over_class_based_filters_with_the_same_key(): void
    {
        User::factory()->create(['age' => 30]);
        User::factory()->create(['age' => 10]);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::exact('age_more_than', 'age')])
            ->filters(['age_more_than' => 30])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals(30, $users->first()->age);
    }
}
