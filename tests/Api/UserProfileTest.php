<?php

namespace Tests\Api;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\ApiTestCase;

class UserProfileTest extends ApiTestCase
{
    /** @test */
    public function member_can_view_their_own_profile()
    {
        $randomUser = factory(User::class)
            ->states(Role::ROLE_ADMIN)
            ->create($userCred = [
                'email' => 'random-member@example.co',
                'password' => 'password',
                'active' => true,
                'email_verified_at' => now()->format('Y-m-d H:i:s')
            ]);

        $jsonResponse = $this->actingAsUser($userCred)
            ->getJson('profile')
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id', 'first_name', 'last_name', 'email'
                ]
            ]);

        tap($jsonResponse->decodeResponseJson(), function ($response) use ($randomUser) {
            $data = $response['data'];
            $user = User::find($data['id']);
            $this->assertTrue($randomUser->is($user));
        });
    }

    /** @test */
    public function member_can_update_their_own_profile()
    {
        $memberUser = [
            'first_name' => 'Member',
            'last_name' => 'User',
            'email' => 'member-user@example.com',
            'password' => 'password',
            'active' => true,
            'email_verified_at' => now()->format('Y-m-d H:i:s')
        ];

        $user = factory(User::class)->create($memberUser);

        $jsonResponse = $this
            ->actingAsUser([
                'email' => $memberUser['email'],
                'password' => $memberUser['password']
            ])
            ->patchJson("profile/{$user->getKey()}", $payload = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com'
            ])
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id', 'first_name', 'last_name', 'email'
                ]
            ]);

        tap($jsonResponse->decodeResponseJson(), function ($response) use ($payload) {
            $data = $response['data'];
            $updatedUser = User::find($data['id']);
            $this->assertEquals($updatedUser->first_name, $payload['first_name']);
            $this->assertEquals($updatedUser->last_name, $payload['last_name']);
            $this->assertEquals($updatedUser->email, $payload['email']);
        });
    }

    /** @test */
    public function it_can_not_update_other_users_profile()
    {
        factory(User::class)->create($user1Credentials = [
            'email' => 'user1@example.com',
            'password' => 'password',
            'active' => true,
            'email_verified_at' => now()->format('Y-m-d H:i:s')
        ]);

        $user2 = factory(User::class)->create($user2Credentials = [
            'email' => 'user2@example.com',
            'password' => 'password',
            'active' => true,
            'email_verified_at' => now()->format('Y-m-d H:i:s')
        ]);

        $jsonResponse = $this
            ->actingAsUser([
                'email' => $user1Credentials['email'],
                'password' => $user1Credentials['password']
            ])
            ->patchJson("profile/{$user2->getKey()}", $payload = [
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]);

        $jsonResponse->assertStatus(401);
    }

    /** @test */
    public function it_can_update_user_password()
    {
        $user = factory(User::class)->create($userCredentials = [
            'email' => 'user1@example.com',
            'password' => 'password',
            'active' => true,
            'email_verified_at' => now()->format('Y-m-d H:i:s')
        ]);

        $this
            ->actingAsUser([
                'email' => $userCredentials['email'],
                'password' => $userCredentials['password']
            ])
            ->patchJson("profile/{$user->getKey()}/password", [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])->assertSuccessful();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
