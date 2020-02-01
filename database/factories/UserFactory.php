<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

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

$roles = Role::all();

$factory->define(User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'active' => (bool) random_int(0, 1),
        'password' => 'secret',
        'remember_token' => Str::random(10),
    ];
});

$factory->state(User::class, Role::ROLE_ADMIN, function (Faker $faker) use ($roles) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => microtime(true) . '_' . $faker->unique()->safeEmail,
        'password' => 'password',
        'email_verified_at' => now()->format('Y-m-d H:i:s'),
        'active' => true,
    ];
});

// After creating super admin user, assign admin role and permissions
$factory->afterCreatingState(User::class, Role::ROLE_ADMIN, function (User $user) use ($roles) {
    // @todo: assign admin role and permissions
    $user->attachRoles([
        $roles->firstWhere('name', Role::ROLE_MEMBER),
        $roles->firstWhere('name', Role::ROLE_ADMIN)
    ]);
});

$factory->state(User::class, Role::ROLE_MEMBER, function (Faker $faker) use ($roles) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => microtime(true) . '_' . $faker->unique()->safeEmail,
        'active' => (bool) random_int(0, 1),
        'password' => 'secret',
        'remember_token' => Str::random(10),
        'email_verified_at' => function () {
            return now()->format('Y-m-d H:i:s');
        }
    ];
});

// After creating member user, assign admin role and permissions
$factory->afterCreatingState(User::class, Role::ROLE_MEMBER, function (User $user) use ($roles) {
    // @todo: assign member role and permissions
    $user->attachRoles([
        $roles->firstWhere('name', Role::ROLE_MEMBER)
    ]);
});
