<?php

use Faker\Generator as Faker;
use Fouladgar\EloquentBuilder\Tests\Models\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'age'      => $faker->numberBetween(15, 90),
        'gender'   => $faker->randomElement(['male', 'female']),
        'status'   => $faker->randomElement(['offline', 'online']),
    ];
});
