<?php

/** @var Factory $factory */
use Faker\Generator as Faker;
use Fouladgar\EloquentBuilder\Tests\Models\Post;
use Illuminate\Database\Eloquent\Factory;

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

$factory->define(Post::class, static function (Faker $faker) {
    return [
        'title'        => $faker->title,
        'content'      => $faker->paragraph,
        'is_published' => $faker->boolean,
    ];
});

$factory->state(Post::class, 'true', [
    'is_published' => true,
]);

$factory->state(Post::class, 'false', [
    'is_published' => false,
]);
