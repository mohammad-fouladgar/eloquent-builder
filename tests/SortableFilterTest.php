<?php

declare(strict_types=1);

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Exceptions\FilterException;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class SortableFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_make_query_for_sorting_convention(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['birth_date' => 'DESC', 'score' => 'asc']])
            ->thenApply()
            ->select(['id', 'birth_date', 'score']);

        $users2 = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['birth_date:DESC', 'score:asc']])
            ->thenApply()
            ->select(['id', 'birth_date', 'score']);

        $this->assertEquals(
            'select "id", "birth_date", "score" from "users" order by "birth_date" desc, "score" asc',
            $users->toSql()
        );

        $this->assertEquals(
            'select "id", "birth_date", "score" from "users" order by "birth_date" desc, "score" asc',
            $users2->toSql()
        );
    }

    /**
     * @test
     */
    public function it_can_make_query_for_default_sorting_convention(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['birth_date', 'score:desc']])
            ->thenApply()
            ->select(['id', 'birth_date', 'score']);

        $this->assertEquals(
            'select "id", "birth_date", "score" from "users" order by "birth_date" asc, "score" desc',
            $users->toSql()
        );
    }

    /**
     * @test
     */
    public function it_can_sort_using_a_custom_closure_resolver(): void
    {
        $users = $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['posts_count' => 'desc']])
            ->thenApply();

        $this->assertEquals(
            'select "users".*, (select count(*) from "posts" where "users"."id" = "posts"."user_id" and "posts"."deleted_at" is null) as "posts_count" from "users" order by "posts_count" desc',
            $users->toSql()
        );
    }

    /**
     * @test
     */
    public function it_cannot_bypass_the_sortable_whitelist_via_a_numeric_string_key(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['1' => 'desc']])
            ->thenApply();
    }

    /**
     * @test
     */
    public function it_can_throw_exception_if_selected_column_is_invalid(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['id_is_invalid' => 'DESC', 'score' => 'asc']])
            ->thenApply()
            ->select(['id', 'birth_date', 'score']);
    }

    /**
     * @test
     */
    public function it_can_throw_exception_if_selected_direction_is_invalid(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->model(User::class)
            ->filters(['sort_by' => ['birth_date' => 'DSC', 'score' => 'asc']])
            ->thenApply()
            ->select(['id', 'birth_date', 'score']);
    }
}
