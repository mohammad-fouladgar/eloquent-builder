<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AuthorizingFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_not_authorize_filter_to_apply()
    {
        $this->expectException(AuthorizationException::class);

        User::factory()->create(['status' => 'online']);

        $this->eloquentBuilder->to(User::class, ['status' => 'online'])->count();
    }

    /** @test */
    public function it_can_passes_authorize_and_apply_filter()
    {
        User::factory()->create(['age' => 12]);
        User::factory()->create(['age' => 19]);

        $userCount = $this->eloquentBuilder->to(User::class, ['age_more_than' => 18])->count('id');

        $this->assertEquals(1, $userCount);
    }
}
