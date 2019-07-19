<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class AuthorizingFilterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->eloquentBuilder = new EloquentBuilder(new FilterFactory());
    }

    /**
     * @test
     * @expectedException \Illuminate\Auth\Access\AuthorizationException
     */
    public function it_does_not_authorize_filter_to_apply()
    {
        factory(User::class)->create(['status'=>'online']);

        $this->eloquentBuilder
             ->to(User::class, ['status'=> 'online'])
             ->count();
    }

    /** @test*/
    public function it_can_passes_authorize_and_apply_filter()
    {
        factory(User::class)->create(['age'=>12]);
        factory(User::class)->create(['age'=>19]);

        $userCount = $this->eloquentBuilder
                          ->to(User::class, ['age_more_than'=>18])
                          ->count('id');

        $this->assertEquals(1, $userCount);
    }
}
