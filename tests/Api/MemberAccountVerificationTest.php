<?php

namespace Tests\Api;

use App\Models\User;
use App\Notifications\Auth\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MemberAccountVerificationTest extends TestCase
{
    /** @test */
    public function it_can_verify_email()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);
        $url = app('api.url')
            ->version('v1')
            ->temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                $user->getKey()
            );

        $jsonResponse = $this->json('POST', $url);

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
    public function it_can_not_verify_if_already_verified()
    {
        $user = factory(User::class)->create();
        $url = app('api.url')
            ->version('v1')
            ->temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                $user->getKey()
            );

        $this->postJson($url)
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Email has been been verified already.']);
    }

    /** @test */
    public function it_can_not_verify_if_url_has_invalid_signature()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);

        $response = $this->json('POST', "/email/verify/{$user->getKey()}", []);

        $response->assertStatus(400)
            ->assertJsonFragment(['message' => 'The verification link is invalid.']);
    }

    /** @test */
    public function it_resend_verification_notification()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);

        Notification::fake();

        $this->postJson('email/resend', ['email' => $user->email])
            ->assertSuccessful();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function it_can_not_resend_verification_notification_if_email_does_not_exist()
    {
        $this->postJson('email/resend', ['email' => 'foo@bar.com'])
            ->assertStatus(422)
            ->assertJsonFragment(['errors' => ['email' => ['We can\'t find a user with that e-mail address.']]]);
    }


    /** @test */
    public function it_can_not_resend_verification_notification_if_email_already_verified()
    {
        $user = factory(User::class)->create();

        Notification::fake();

        $this->postJson('email/resend', ['email' => $user->email])
            ->assertStatus(422)
            ->assertJsonFragment(['errors' => ['email' => ['Email has been been verified already.']]]);


        Notification::assertNotSentTo($user, VerifyEmail::class);
    }
}
