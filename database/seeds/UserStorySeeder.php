<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserStorySeeder extends Seeder
{
    const TEST_ADMIN_EMAIL = 'admin@example.com';
    const TEST_ADMIN_PASSWORD = 'password2019';

    const TEST_MEMBER_EMAIL = 'member@example.com';
    const TEST_MEMBER_PASSWORD = 'password2019';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create an admin user
        factory(User::class)->states(Role::ROLE_ADMIN)->create([
            'email'        => static::TEST_ADMIN_EMAIL,
            'password'     => static::TEST_ADMIN_PASSWORD
        ]);

        // Create an member user
        factory(User::class)->states(Role::ROLE_MEMBER)->create([
            'email'        => static::TEST_MEMBER_EMAIL,
            'password'     => static::TEST_MEMBER_PASSWORD,
            'active'       => true,
        ]);
    }
}
