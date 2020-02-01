<?php

namespace Tests\Unit;

use App\Services\Utilities;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MemberRegistrationTest extends TestCase
{
    /**
     * @todo:
     * Registration tests
     * 1. Registered event and SendEmailVerificationNotification listener are invoked which sends a verification email is sent the to the user
     *
     * VerificationController test
     * 1. user roles assignment, only assigned as member, not admin or moderators
     * 2. user receives an email to confirm email address
     * 3. user activates account by clicking the activation link
     * 4. user can reset password
     * 5. user can only update their profile after activating their account
     * 6. active user can update their profile
     */

    /** @test */
    public function it_can_register_user()
    {
        $jsonResponse = $this
            ->json('POST', '/auth/registration', $payload = [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'member-user@example.com',
                'password' => 'secret',
                'passwordConfirmation' => 'secret'
            ]);

        $jsonResponse
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'active'
                ]
            ]);

        tap($jsonResponse->decodeResponseJson(), function ($response) use ($payload) {
            $payload = Utilities::snakeCaseArrayKeys($payload);
            $data = $response['data'];
            $user = User::find($data['id']);
            $this->assertContains($data['id'], User::all()->pluck('user_id')->toArray());
            $this->assertEquals($data['email'], $payload['email']);
            $this->assertEquals($data['first_name'], $payload['first_name']);
            $this->assertEquals($data['last_name'], $payload['last_name']);
            $this->assertEquals($data['active'], false);

            $this->assertTrue(Hash::check('secret', $user->password));
            $this->assertEquals($payload['email'], $user->email);
        });
    }

    /** @test */
    public function it_sends_a_verification_email_after_registration()
    {
        $listener = \Mockery::spy(SendEmailVerificationNotification::class);
        app()->instance(SendEmailVerificationNotification::class, $listener);

        $jsonResponse = $this->json('POST', 'auth/registration', $payload = $this->validParams());

        $jsonResponse->assertStatus(201);

        $response = $jsonResponse->decodeResponseJson();

        $listener->shouldHaveReceived('handle')->with(\Mockery::on(function ($event) use ($response) {
            return $event->user->getKey() === $response['data']['id'];
        }))->once();
    }

    /**
     * @todo: uncomment the test when the following issue is resolved with telescope
     * https://github.com/laravel/telescope/issues/573
     *
     *  /** @test *
     *  public function it_dispatches_a_registered_event_after_registration()
     *  {
     *  Event::fake([Registered::class]);
     *
     *  $jsonResponse = $this->json('POST', 'auth/registration', $payload = $this->validParams());
     *
     *  $jsonResponse->assertStatus(201);
     *
     *  $response = $jsonResponse->decodeResponseJson();
     *
     *  Event::assertDispatched(Registered::class, function ($event) use ($response) {
     *      return $event->user->getKey() === $response['data']['id'];
     *  });
     *  }
     */

    /** @test */
    public function it_dispatches_a_registered_event_after_registration()
    {
        Event::fake([Registered::class]);

        $jsonResponse = $this->json('POST', 'auth/registration', $payload = $this->validParams());

        $jsonResponse->assertStatus(201);

        Event::assertDispatched(Registered::class, function ($event) use ($jsonResponse) {
            $response = $jsonResponse->decodeResponseJson();
            return $event->user->getKey() === $response['data']['id'];
        });
    }

    /** @test */
    public function it_does_not_make_the_user_active_after_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams());

        $jsonResponse->assertStatus(201);

        tap($jsonResponse->decodeResponseJson(), function ($response) use ($payload) {
            $data = $response['data'];
            $user = User::find($data['id']);

            $this->assertEquals($data['active'], false);
            $this->assertEquals($user->active, false);
            $this->assertEquals($user->email_verified_at, null);
        });
    }

    /** @test */
    public function first_name_is_required_for_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'first_name' => ''
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('first_name', $errors);
        });
    }

    /** @test */
    public function last_name_is_required_for_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'last_name' => ''
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('last_name', $errors);
        });
    }

    /** @test */
    public function email_is_required_for_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'email' => ''
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('email', $errors);
        });
    }

    /** @test */
    public function email_must_be_a_valid_email_address_for_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'email' => 'not-an-email-address'
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('email', $errors);
        });
    }

    /** @test */
    public function email_must_be_unique_for_registration()
    {
        $uniqueEmail = 'unique-email@example.com';
        factory(User::class)->create([
            'email' => $uniqueEmail
        ]);
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'email' => $uniqueEmail
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('email', $errors);
        });
    }

    /** @test */
    public function password_is_required_for_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'password' => ''
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('password', $errors);
        });
    }

    /** @test */
    public function password_confirmation_must_match_with_password_for_registration()
    {
        $jsonResponse = $this
            ->json('POST', 'auth/registration', $payload = $this->validParams([
                'password' => 'secret',
                'password_confirmation' => 'not-secret'
            ]));

        $jsonResponse->assertStatus(422);

        tap($jsonResponse->getOriginalContent(), function ($errors) {
            $this->assertArrayHasKey('password', $errors);
        });
    }

     /** @test */
     public function password_should_be_at_least_6_char_long_for_registration()
     {
         $jsonResponse = $this
             ->json('POST', 'auth/registration', $payload = $this->validParams([
                 'password' => '12345',
                 'password_confirmation' => '12345'
             ]));

         $jsonResponse->assertStatus(422);

         tap($jsonResponse->getOriginalContent(), function ($errors) {
             $this->assertArrayHasKey('password', $errors);
         });
     }

    protected function validParams($overrides = [])
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'member-user@example.com',
            'password' => 'secret',
            'password_confirmation' => 'secret'
        ], $overrides);
    }
}
