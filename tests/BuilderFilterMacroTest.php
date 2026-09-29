<?php

declare(strict_types=1);

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\QuickFilter;
use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

class BuilderFilterMacroTest extends TestCase
{
    /** @test */
    public function it_can_filter_via_the_builder_macro_on_a_model_class(): void
    {
        User::factory()->create(['gender' => 'male']);
        User::factory()->create(['gender' => 'female']);

        $users = User::filter(['gender' => 'female'])->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals('female', $users->first()->gender);
    }

    /** @test */
    public function it_can_filter_via_the_builder_macro_on_an_existing_query(): void
    {
        User::factory()->create(['gender' => 'male', 'age' => 40]);
        User::factory()->create(['gender' => 'female', 'age' => 40]);
        User::factory()->create(['gender' => 'female', 'age' => 10]);

        $users = User::where('age', 40)->filter(['gender' => 'female'])->get();

        $this->assertEquals(1, $users->count());
    }

    /** @test */
    public function it_returns_an_eloquent_builder_instance(): void
    {
        $this->assertInstanceOf(Builder::class, User::filter(['gender' => 'female']));
    }

    /** @test */
    public function it_does_not_leak_quick_filters_from_a_prior_facade_call(): void
    {
        $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::exact('status')])
            ->filters(['status' => 'online'])
            ->thenApply();

        $this->expectException(AuthorizationException::class);

        User::filter(['status' => 'online'])->get();
    }
}
