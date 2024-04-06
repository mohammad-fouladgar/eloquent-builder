<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Fouladgar\EloquentBuilder\Tests\Models\User;

class CustomFilterTest extends TestCase
{
    /**
     * @test
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
                                       ->model(User::class)
                                       ->filters(['age_more_than' => 25])
                                       ->thenApply()
                                       ->get();

        $posts = $this->eloquentBuilder->setFilterNamespace('Fouladgar\\EloquentBuilder\\Tests\\PostDomain\\CustomFilters\\')
                                       ->model(Post::class)
                                       ->filters(['is_published' => true])
                                       ->thenApply()
                                       ->get();

        $this->assertEquals(2, $users->count());
        $this->assertEquals(3, $posts->count());
    }
}
