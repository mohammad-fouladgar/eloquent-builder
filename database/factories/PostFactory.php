<?php

namespace Fouladgar\EloquentBuilder\Database\Factories;

use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title'        => $this->faker->title,
            'content'      => $this->faker->paragraph,
            'is_published' => $this->faker->boolean,
        ];
    }
}

