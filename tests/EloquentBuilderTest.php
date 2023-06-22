<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Exceptions\FilterInstanceException;
use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EloquentBuilderTest extends TestCase
{
    /** @test */
    public function it_can_make_without_filters()
    {
        $this->assertInstanceOf(Builder::class, $this->eloquentBuilder->to(User::class));
    }

    /**
     * @test
     */
    public function it_should_return_not_found_filter_exception()
    {
        $this->expectException(NotFoundFilterException::class);
        $this->eloquentBuilder->to(User::class, ['not_exists_filter' => 'any_value']);
    }

    /**
     * @test
     */
    public function it_should_return_invalid_argument_exception()
    {
        $this->expectException(FilterInstanceException::class);
        $this->eloquentBuilder->to(User::class, ['invalid_implemented' => 'any_value']);
    }

    /** @test */
    public function it_can_make_with_filters()
    {
        $this->assertInstanceOf(
            Builder::class,
            $this->eloquentBuilder->to(User::class, ['age_more_than' => 25])
        );
    }

    /** @test */
    public function it_should_return_builder_with_an_instance_of_the_model()
    {
        $userInstance = new User();

        $this->assertInstanceOf(Builder::class, $this->eloquentBuilder->to($userInstance));
    }

    /** @test */
    public function it_can_make_with_existing_query()
    {
        User::factory()->create(['age' => 30, 'gender' => 'male']);

        $users = $this->eloquentBuilder->to(
            User::where('age', '>', 20),
            ['gender' => 'male']
        );

        $this->assertEquals(
            'select * from "users" where "age" > ? and "gender" = ?',
            str_replace('`', '"', $users->toSql())
        );

        $this->assertEquals(1, $users->count('id'));
    }

    /** @test */
    public function it_can_get_user_list_where_age_more_than_25()
    {
        User::factory()->create(['age' => 15]);
        User::factory()->create(['age' => 20]);
        User::factory()->create(['age' => 22]);
        User::factory()->create(['age' => 30]);
        User::factory()->create(['age' => 40]);

        $users = $this->eloquentBuilder->to(User::class, ['age_more_than' => 25])->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test */
    public function it_can_get_all_users_that_have_at_least_one_published_post()
    {
        User::factory(3)
            ->create()
            ->each(function ($user) {
                $user->posts()->save(Post::factory()->make(['is_published' => true]));
            });

        User::factory(2)
            ->create()
            ->each(function ($user) {
                $user->posts()->save(Post::factory()->make(['is_published' => false]));
            });

        $users = $this->eloquentBuilder->to(User::class, ['published_post' => true])->get();

        $this->assertEquals(5, User::get()->count());
        $this->assertEquals(5, Post::get()->count());
        $this->assertEquals(3, $users->count());
    }

    /** @test */
    public function it_can_get_female_users_over_30_years_old()
    {
        User::factory()->create(['gender' => 'male', 'age' => 31]);
        User::factory()->create(['gender' => 'female', 'age' => 25]);
        User::factory()->create(['gender' => 'female', 'age' => 35]);
        User::factory()->create(['gender' => 'female', 'age' => 40]);

        $users = $this->eloquentBuilder->to(User::class, ['age_more_than' => 30, 'gender' => 'female'])->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test */
    public function it_can_ignore_filters_lacking_value()
    {
        User::factory()
            ->create()
            ->each(function ($user) {
                $user->posts()->save(Post::factory()->make(['is_published' => true]));
            });

        User::factory()->create();

        $users = $this->eloquentBuilder->to(
            User::class,
            ['published_post' => true, 'gender' => null, 'age_more_than' => '', 'name']
        )->get();

        $this->assertEquals(1, $users->count());
    }

    /** @test */
    public function it_can_work_with_then_apply_method()
    {
        User::factory()->create(['gender' => 'male', 'age' => 31]);
        User::factory()->create(['gender' => 'female', 'age' => 25]);
        User::factory()->create(['gender' => 'female', 'age' => 35]);
        User::factory()->create(['gender' => 'female', 'age' => 40]);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['age_more_than' => 30, 'gender' => 'female'])
            ->thenApply()
            ->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test */
    public function it_can_work_when_filters_is_empty()
    {
        User::factory()->create(['gender' => 'male', 'age' => 31]);
        User::factory()->create(['gender' => 'female', 'age' => 25]);
        User::factory()->create(['gender' => 'female', 'age' => 35]);
        User::factory()->create(['gender' => 'female', 'age' => 40]);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters()
            ->thenApply()
            ->get();

        $this->assertEquals(4, $users->count());
    }

    /** @test */
    public function it_can_work_by_pushing_filters()
    {
        User::factory()->create(['gender' => 'male', 'age' => 31]);
        User::factory()->create(['gender' => 'female', 'age' => 25]);
        User::factory()->create(['gender' => 'female', 'age' => 35]);
        User::factory()->create(['gender' => 'female', 'age' => 40]);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['name' => null])
            ->filter(['published_at' => null])
            ->filter(['age_more_than' => 30])
            ->filter(['gender' => 'female'])
            ->thenApply()
            ->get();

        $this->assertEquals(2, $users->count());
    }
}
