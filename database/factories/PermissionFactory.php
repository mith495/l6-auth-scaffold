<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Permission;
use Faker\Generator as Faker;

$factory->define(Permission::class, function (Faker $faker) {
    $name = $faker->word;
    return [
        'name' => $name,
        'display_name' => $name,
        'description' => $faker->sentence
    ];
});
