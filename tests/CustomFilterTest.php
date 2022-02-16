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
        User::factory()->create(['age' => 15]);
        User::factory()->create(['age' => 20]);
        User::factory()->create(['age' => 30]);
        User::factory()->create(['age' => 40]);

        Post::factory(3)->create(['user_id' => 20, 'is_published' => true]);
        Post::factory(2)->create(['user_id' => 20, 'is_published' => false]);

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
