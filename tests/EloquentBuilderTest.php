<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EloquentBuilderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->eloquentBuilder = new EloquentBuilder(new FilterFactory());
    }

    /** @test */
    public function it_can_make_without_filters()
    {
        $this->assertInstanceOf(
            Builder::class,
            $this->eloquentBuilder->to(User::class)
        );
    }

    /**
     * @test
     * @expectedException  \Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException
     */
    public function it_should_return_not_found_filter_exception()
    {
        $this->eloquentBuilder->to(User::class, ['not_exists_filter'=>'any_value']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_should_return_invalid_argument_exception()
    {
        $this->eloquentBuilder->to(User::class, ['invalid_implemented'=>'any_value']);
    }

    /** @test */
    public function it_can_make_with_filters()
    {
        $this->assertInstanceOf(
            Builder::class,
            $this->eloquentBuilder->to(User::class, ['age_more_than'=>25])
        );
    }

    /** @test */
    public function it_can_make_with_existing_query()
    {
        factory(User::class)->create(['age'=>30, 'gender'=>'male']);

        $users = $this->eloquentBuilder->to(
            User::where('age', '>', 20),
            ['gender'=> 'male']
        );

        $this->assertEquals(
            'select * from "users" where "age" > ? and "gender" = ?',
            $users->toSql()
        );

        $this->assertEquals(1, $users->count('id'));
    }

    /** @test */
    public function it_can_get_user_list_where_age_more_than_25()
    {
        factory(User::class)->create(['age'=>15]);
        factory(User::class)->create(['age'=>20]);
        factory(User::class)->create(['age'=>22]);

        factory(User::class)->create(['age'=>30]);
        factory(User::class)->create(['age'=>40]);

        $users = $this->eloquentBuilder->to(User::class, ['age_more_than'=>25])->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test */
    public function it_can_get_all_users_that_have_at_least_one_published_post()
    {
        factory(User::class, 3)
            ->create()
            ->each(function ($user) {
                $user->posts()
                        ->save(
                        factory(Post::class)->make(['is_published'=>true])
                    );
            });

        factory(User::class, 2)
            ->create()
            ->each(function ($user) {
                $user->posts()
                        ->save(
                        factory(Post::class)->make(['is_published'=>false])
                    );
            });

        $users = $this->eloquentBuilder->to(User::class, ['published_post'=>true])->get();

        $this->assertEquals(5, User::get()->count());
        $this->assertEquals(5, Post::get()->count());
        $this->assertEquals(3, $users->count());
    }

    /** @test */
    public function it_can_get_female_users_over_30_years_old()
    {
        factory(User::class)->create(['gender'=>'male',   'age'=>31]);
        factory(User::class)->create(['gender'=>'female', 'age'=>25]);
        factory(User::class)->create(['gender'=>'female', 'age'=>35]);
        factory(User::class)->create(['gender'=>'female', 'age'=>40]);

        $users = $this->eloquentBuilder->to(User::class, ['age_more_than'=>30, 'gender'=>'female'])->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test*/
    public function it_can_ignore_filters_lacking_value()
    {
        factory(User::class, 1)
            ->create()
            ->each(function ($user) {
                $user
                    ->posts()
                    ->save(
                        factory(Post::class)->make(['is_published'=>true])
                    );
            });

        factory(User::class)->create();

        $users = $this->eloquentBuilder->to(
            User::class,
            ['published_post'=> true, 'gender'=> null, 'age_more_than'=>'', 'name']
        )->get();

        $this->assertEquals(1, $users->count());
    }
}
