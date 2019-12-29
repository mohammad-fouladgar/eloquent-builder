<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\EloquentBuilder;
use Fouladgar\EloquentBuilder\Exceptions\NotFoundFilterException;
use Fouladgar\EloquentBuilder\Support\Foundation\Concrete\FilterFactory;
use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Fouladgar\EloquentBuilder\Tests\Models\User;

/**
 * @property EloquentBuilder eloquentBuilder
 */
class CustomFilterTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->eloquentBuilder = new EloquentBuilder(new FilterFactory());
    }

    /**
     * @test
     *
     * @throws NotFoundFilterException
     */
    public function it_can_resolve_namespaces_per_domain(): void
    {
        factory(User::class)->create(['age' => 15]);
        factory(User::class)->create(['age' => 20]);
        factory(User::class)->create(['age' => 30]);
        factory(User::class)->create(['age' => 40]);

        factory(Post::class, 3)->state('true')->create(['user_id' => 20]);
        factory(Post::class, 2)->state('false')->create(['user_id' => 20]);

        $users = $this->eloquentBuilder->setFilterNamespace('Fouladgar\\EloquentBuilder\\Tests\\UserDomain\\CustomFilters\\')
                                       ->to(User::class, ['age_more_than' => 25])
                                       ->get();

        $posts = $this->eloquentBuilder->setFilterNamespace('Fouladgar\\EloquentBuilder\\Tests\\PostDomain\\CustomFilters\\')
                                       ->to(Post::class, ['is_published' => true])
                                       ->get();

        $this->assertEquals(2, $users->count());
        $this->assertEquals(3, $posts->count());
    }
}
