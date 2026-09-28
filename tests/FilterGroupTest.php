<?php

declare(strict_types=1);

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterGroup;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\QuickFilter;
use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class FilterGroupTest extends TestCase
{
    /** @test */
    public function it_can_or_quick_filters_together(): void
    {
        User::factory()->create(['gender' => 'male', 'status' => 'offline']);
        User::factory()->create(['gender' => 'female', 'status' => 'offline']);
        User::factory()->create(['gender' => 'male', 'status' => 'online']);
        User::factory()->create(['gender' => 'female', 'status' => 'online']);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::exact('gender'), QuickFilter::exact('status')])
            ->filterGroups([FilterGroup::or(['gender', 'status'])])
            ->filters(['gender' => 'female', 'status' => 'online'])
            ->thenApply()
            ->get();

        $this->assertEquals(3, $users->count());
    }

    /** @test */
    public function it_can_or_class_based_filters_together(): void
    {
        User::factory()->create(['name' => 'John', 'age' => 15, 'gender' => 'male']);
        User::factory()->create(['name' => 'John', 'age' => 30, 'gender' => 'male']);
        User::factory()->create(['name' => 'Alice', 'age' => 30, 'gender' => 'male']);
        User::factory()->create(['name' => 'John', 'age' => 15, 'gender' => 'female']);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filterGroups([FilterGroup::or(['age_more_than', 'gender'])])
            ->filters(['name' => 'John', 'age_more_than' => 20, 'gender' => 'female'])
            ->thenApply()
            ->get();

        $this->assertEquals(2, $users->count());
    }

    /** @test */
    public function it_applies_a_single_present_group_member_like_a_normal_filter(): void
    {
        User::factory()->create(['gender' => 'female']);
        User::factory()->create(['gender' => 'male']);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::exact('gender'), QuickFilter::exact('status')])
            ->filterGroups([FilterGroup::or(['gender', 'status'])])
            ->filters(['gender' => 'female'])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $users->count());
    }

    /** @test */
    public function it_skips_the_group_entirely_when_no_member_has_a_value(): void
    {
        User::factory()->create(['age' => 30]);
        User::factory()->create(['age' => 10]);

        $users = $this->eloquentBuilder
            ->model(User::class)
            ->quickFilters([QuickFilter::exact('gender'), QuickFilter::exact('status')])
            ->filterGroups([FilterGroup::or(['gender', 'status'])])
            ->filters(['age_more_than' => 20])
            ->thenApply()
            ->get();

        $this->assertEquals(1, $users->count());
    }

    /** @test */
    public function it_still_authorizes_grouped_class_based_filters(): void
    {
        $this->expectException(AuthorizationException::class);

        User::factory()->create(['status' => 'online']);

        $this->eloquentBuilder
            ->model(User::class)
            ->filterGroups([FilterGroup::or(['status', 'gender'])])
            ->filters(['status' => 'online', 'gender' => 'male'])
            ->thenApply()
            ->get();
    }
}
