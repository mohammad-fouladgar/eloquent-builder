<?php

namespace Fouladgar\EloquentBuilder\Tests;

use BadMethodCallException;
use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class NumberFilterTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['score' => 100]);
        User::factory()->create(['score' => 200]);
        User::factory()->create(['score' => 300]);
        User::factory()->create(['score' => 400]);
        User::factory()->create(['score' => 500]);
    }

    /**
     * @test
     */
    public function it_can_filter_between_number_by_using_array(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => [300, 500]])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_between_number_by_using_convention(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'between:300,500'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_between_number_by_simple_value_separated_by_comma(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => '300,500'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_less_than_number(): void
    {
        $ltNumber = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'lt:300'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(2, $ltNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_less_than__or_equals_a_number(): void
    {
        $lteNumber = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'lte:300'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(3, $lteNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_greater_than_a_number(): void
    {
        $gtNumber = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'gt:300'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(2, $gtNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_greater_than_or_equals_a_number(): void
    {
        $gteNumber = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'gte:300'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(3, $gteNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_equal_a_number(): void
    {
        $defaultNumber = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => '500'])
            ->thenApply()
            ->get(['id', 'score']);

        $equalNumber = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'equals:500'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->assertEquals(1, $defaultNumber->count());
        $this->assertEquals(1, $equalNumber->count());
    }

    /**
     * @test
     */
    public function it_can_throw_validate_convention_exception_if_number_is_not_a_valid_numeric(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'invalid_numeric'])
            ->thenApply()
            ->get(['id', 'score']);
    }

    /**
     * @test
     */
    public function it_can_throw_validate_convention_exception_if_numbers_are_not_a_valid_numeric(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'between:300,200A'])
            ->thenApply()
            ->get(['id', 'score']);

        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => ['invalid_numeric_1', 'invalid_numeric_2']])
            ->thenApply()
            ->get(['id', 'score']);

        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'invalid_numeric_1,invalid_numeric_2'])
            ->thenApply()
            ->get(['id', 'score']);
    }

    /**
     * @test
     */
    public function it_can_throw_bad_method_call_exception_if_a_filter_number_does_not_exist(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['score' => 'does_not_exists_method:400'])
            ->thenApply()
            ->get(['id', 'score']);
    }
}
