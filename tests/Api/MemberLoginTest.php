<?php

namespace Tests\Api;

use Tests\TestCase;
use App\Models\User;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Symfony\Component\HttpFoundation\HeaderBag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemberLoginTest extends TestCase
{
    /** @test */
    public function only_active_user_can_login()
    {
        factory(User::class)->create($credentials = [
            'email' => 'member-user@example.com',
            'password' => 'secret',
            'active' => true

        ]);

        $jsonResponse = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode($credentials['email'] . ':' . $credentials['password']),
        ])->json('GET', 'auth/login');

        $jsonResponse
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'data' => [
                        'jwt', 'token_type', 'expires_in',
                    ]
                ]
            )
            ->assertJson([
                'data' => [
                    'token_type' => 'bearer',
                ]
            ]);
    }

    /** @test */
    public function it_will_not_log_an_invalid_user_in()
    {
        $jsonResponse = $this->withHeaders([
            'Authorization' => 'Basic ' . base64_encode('not-registered-email@example.com' . ':' . 'not-legit-password'),
        ])->json('GET', 'auth/login');

        $jsonResponse->assertStatus(401);
    }
}
