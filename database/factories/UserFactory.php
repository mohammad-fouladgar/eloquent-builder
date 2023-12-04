<?php

namespace Fouladgar\EloquentBuilder\Database\Factories;

use Fouladgar\EloquentBuilder\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name'   => $this->faker->name,
            'age'    => $this->faker->numberBetween(15, 90),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'status' => $this->faker->randomElement(['offline', 'online']),
        ];
    }
}


