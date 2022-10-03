<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Exceptions\ValidateConventionException;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class NumberFilterTest extends TestCase
{
    private EloquentBuilder $eloquentBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->eloquentBuilder = new EloquentBuilder(new FilterFactory());

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
            ->to(User::class, ['score' => [300, 500]])
            ->get(['id', 'score']);

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_between_number_by_using_convention(): void
    {
        $users = $this->eloquentBuilder
            ->to(User::class, ['score' => 'between:300,500'])
            ->get(['id', 'score']);

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_between_number_by_simple_value_separated_by_comma(): void
    {
        $users = $this->eloquentBuilder
            ->to(User::class, ['score' => '300,500'])
            ->get(['id', 'score']);

        $this->assertEquals(3, $users->count());
    }

    /**
     * @test
     */
    public function it_can_filter_less_than_number(): void
    {
        $ltNumber = $this->eloquentBuilder
            ->to(User::class, ['score' => 'lt:300'])
            ->get(['id', 'score']);

        $this->assertEquals(2, $ltNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_less_than__or_equals_a_number(): void
    {
        $lteNumber = $this->eloquentBuilder
            ->to(User::class, ['score' => 'lte:300'])
            ->get(['id', 'score']);

        $this->assertEquals(3, $lteNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_greater_than_a_number(): void
    {
        $gtNumber = $this->eloquentBuilder
            ->to(User::class, ['score' => 'gt:300'])
            ->get(['id', 'score']);

        $this->assertEquals(2, $gtNumber->count());
    }

    /**
     * @test
     */
    public function it_can_filter_greater_than_or_equals_a_number(): void
    {
        $gteNumber = $this->eloquentBuilder
            ->to(User::class, ['score' => 'gte:300'])
            ->get(['id', 'score']);

        $this->assertEquals(3, $gteNumber->count());
    }


    /**
     * @test
     */
    public function it_can_filter_equal_a_number(): void
    {
        $defaultNumber = $this->eloquentBuilder
            ->to(User::class, ['score' => '500'])
            ->get(['id', 'score']);

        $equalNumber = $this->eloquentBuilder
            ->to(User::class, ['score' => 'equals:500'])
            ->get(['id', 'score']);

        $this->assertEquals(1, $defaultNumber->count());
        $this->assertEquals(1, $equalNumber->count());
    }

    /**
     * @test
     */
    public function it_can_throw_validate_convention_exception_if_number_is_not_a_valid_numeric(): void
    {
        $this->expectException(ValidateConventionException::class);
        $this->eloquentBuilder
            ->to(User::class, ['score' => 'invalid_numeric'])
            ->get(['id', 'score']);
    }

    /**
     * @test
     */
    public function it_can_throw_validate_convention_exception_if_numbers_are_not_a_valid_numeric(): void
    {
        $this->expectException(ValidateConventionException::class);
        $this->eloquentBuilder
            ->to(User::class, ['score' => 'between:300,200A'])
            ->get(['id', 'score']);

        $this->expectException(ValidateConventionException::class);
        $this->eloquentBuilder
            ->to(User::class, ['score' => ['invalid_numeric_1', 'invalid_numeric_2']])
            ->get(['id', 'score']);

        $this->expectException(ValidateConventionException::class);
        $this->eloquentBuilder
            ->to(User::class, ['score' => 'invalid_numeric_1,invalid_numeric_2'])
            ->get(['id', 'score']);
    }

    /**
     * @test
     */
    public function it_can_throw_bad_method_call_exception_if_a_filter_number_does_not_exist(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->eloquentBuilder
            ->to(User::class, ['score' => 'does_not_exists_method:400'])
            ->get(['id', 'score']);
    }
}
