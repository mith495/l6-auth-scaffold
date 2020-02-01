<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Role;
use Faker\Generator as Faker;

$factory->define(Role::class, function (Faker $faker) {
    $name = $faker->word;
    return [
        'name' => $name,
        'display_name' => $name,
        'description' => $faker->sentence
    ];
});
