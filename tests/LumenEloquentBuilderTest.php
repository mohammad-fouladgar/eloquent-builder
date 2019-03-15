<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class LumenEloquentBuilderTest extends TestCase
{
    use LumenServiceRegister;

    public function setUp(): void
    {
        parent::setUp();

        $this->eloquentBuilder = new EloquentBuilder(new FilterFactory());
    }

    /** @test */
    public function test_simple_filter()
    {
        factory(User::class)->create(['age'=>10]);
        factory(User::class)->create(['age'=>15]);
        factory(User::class)->create(['age'=>20]);

        $users = $this->eloquentBuilder->to(User::class, ['age_more_than'=>10])->get();

        $this->assertCount(2, $users);
    }
}
