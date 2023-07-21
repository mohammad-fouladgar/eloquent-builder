<?php

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
            ->to(User::class, ['sort_by' => ['birth_date' => 'DESC', 'score' => 'asc']])
            ->select(['id', 'birth_date', 'score']);

        $users2 = $this->eloquentBuilder
            ->to(User::class, ['sort_by' => ['birth_date:DESC', 'score:asc']])
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
            ->to(User::class, ['sort_by' => ['birth_date', 'score:desc']])
            ->select(['id', 'birth_date', 'score']);

        $this->assertEquals(
            'select "id", "birth_date", "score" from "users" order by "birth_date" asc, "score" desc',
            $users->toSql()
        );
    }

    /**
     * @test
     */
    public function it_can_throw_exception_if_selected_column_is_invalid(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->to(User::class, ['sort_by' => ['id_is_invalid' => 'DESC', 'score' => 'asc']])
            ->select(['id', 'birth_date', 'score']);
    }

    /**
     * @test
     */
    public function it_can_throw_exception_if_selected_direction_is_invalid(): void
    {
        $this->expectException(FilterException::class);
        $this->eloquentBuilder
            ->to(User::class, ['sort_by' => ['birth_date' => 'DSC', 'score' => 'asc']])
            ->select(['id', 'birth_date', 'score']);
    }
}
