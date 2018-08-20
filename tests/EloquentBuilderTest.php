<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EloquentBuilderTest extends TestCase
{
    /** @test */
    public function it_can_make_without_filters()
    {
        $builder = EloquentBuilder::to(User::class);

        $this->assertInstanceOf(Builder::class, $builder);
    }

    /** @test */
    public function it_can_make_with_filters()
    {
        $builder = EloquentBuilder::to(User::class, ['age_more_than'=>25, 'not_exists_filter'=>'tom']);

        $this->assertInstanceOf(Builder::class, $builder);
    }

    /** @test */
    public function it_can_make_with_existing_query()
    {
        factory(User::class)->create(['age'=>30, 'gender'=>'male']);

        $query = User::where('age', '>', 20);

        $users = EloquentBuilder::to($query, ['gender'=>'male']);

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

        $users = EloquentBuilder::to(User::class, ['age_more_than'=>25])->get();

        $this->assertEquals(2, $users->count());
    }

    /* @test */
    public function it_can_get_user_list_order_by_id_desc()
    {
        factory(User::class, 5)->create();

        $users = EloquentBuilder::to(User::class, ['sort_by'=>'id'])->get();

        $this->assertEquals(5, $users->first()->id);
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

        $users = EloquentBuilder::to(User::class, ['published_post'=>true])->get();

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

        $users = EloquentBuilder::to(User::class, ['age_more_than'=>30, 'gender'=>'female'])->get();

        $this->assertEquals(2, $users->count());
    }
}
