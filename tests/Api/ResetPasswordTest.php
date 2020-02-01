<?php

namespace Tests\Api;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    /** @test */
    public function it_can_sent_password_reset_link()
    {
        factory(User::class)->create([
            'email' => $email = 'email@exampl.com'
        ]);

        $jsonResponse = $this->postJson('password/email', [
            'email' => $email
        ]);

        $jsonResponse
            ->assertSuccessful()
            ->assertJsonFragment([
                'message' => 'We have e-mailed your password reset link!'
            ]);
    }

    /** @test */
    public function email_is_required_in_password_reset_attempt()
    {
        $this->postJson('password/email', ['email' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function email__must_be_a_valid_email_for_password_reset_attempt()
    {
        $this->postJson('password/email', ['email' => 'NOT-A-VALID-EMAIL'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_reset_the_password_from_the_given_link()
    {
        $user = factory(User::class)->create([
            'email' => $email = 'email@exampl.com',
            'password' => 'password'
        ]);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => $email,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $jsonResponse
            ->assertSuccessful()
            ->assertJsonFragment([
                'message' => 'Your password has been reset!'
            ]);
    }

    /** @test */
    public function email_is_required_for_password_reset()
    {
        $user = factory(User::class)->create([
            'email' => 'email@exampl.com',
            'password' => 'password'
        ]);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => '',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $jsonResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function email_must_be_a_valid_email_for_password_reset()
    {
        $user = factory(User::class)->create([
            'email' => 'email@exampl.com',
            'password' => 'password'
        ]);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => 'NOT-A-VALID-EMAIL',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $jsonResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function token_is_required_for_password_reset()
    {
        factory(User::class)->create([
            'email' => $email = 'email@exampl.com',
            'password' => 'password'
        ]);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => '',
            'email' => $email,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $jsonResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    /** @test */
    public function password_is_required_for_password_reset()
    {
        $user = factory(User::class)->create([
            'email' => $email = 'email@exampl.com',
            'password' => 'password'
        ]);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => $email,
            'password' => '',
            'password_confirmation' => 'secret',
        ]);

        $jsonResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function password_confirmation_must_be_same_as_password_for_password_reset()
    {
        $user = factory(User::class)->create([
            'email' => $email = 'email@exampl.com',
            'password' => 'password'
        ]);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => $email,
            'password' => 'secret',
            'password_confirmation' => '',
        ]);

        $jsonResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function password_must_be_min_6_char_long_for_password_reset()
    {
        $user = factory(User::class)->create([
            'email' => $email = 'email@exampl.com',
            'password' => 'password'
        ]);

        $password = str_repeat('1', 5);

        $jsonResponse = $this->postJson('password/reset', [
            'token' => Password::broker()->createToken($user),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $jsonResponse
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
