<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Tests\Models\User;

/**
 * @property EloquentBuilder eloquentBuilder
 */
class LumenEloquentBuilderTest extends TestCase
{
    use LumenServiceRegister;

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->eloquentBuilder = new EloquentBuilder(new FilterFactory());
    }

    /** @test */
    public function test_simple_filter()
    {
        User::factory()->create(['age' => 10]);
        User::factory()->create(['age' => 15]);
        User::factory()->create(['age' => 20]);

        $users = $this->eloquentBuilder->to(User::class, ['age_more_than' => 10])->get();

        $this->assertCount(2, $users);
    }
}
