<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PasswordResetException;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate(
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
            ],
            []
        );

        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $user = $this->validateReset($credentials);

        if (! $user instanceof CanResetPasswordContract) {
            return new MemberUser($user);
        }

        $password = $credentials['password'];

        $this->getBrokerToken()->delete($user);

        $this->resetPassword($user, $password);

        return response()->json([
            'message' => 'Your password has been reset!'
        ]);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = $this->broker()->getUser($request->only('email'));

        if (is_null($user)) {
            throw new PasswordResetException('We can\'t find a user with that e-mail address.');
        }

        $user->sendPasswordResetNotification(
            $this->broker()->createToken($user)
        );

        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Validate a password reset for the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword|string
     */
    protected function validateReset(array $credentials)
    {
        if (is_null($user = $this->broker()->getUser($credentials))) {
            throw new PasswordResetException('We can\'t find a user with that e-mail address.');
        }

        if (! $this->validatePasswordWithDefaults($credentials)) {
            throw new PasswordResetException('Passwords must be at least six characters and match the confirmation.');
        }

        if (! $this->getBrokerToken()->exists($user, $credentials['token'])) {
            throw new PasswordResetException('This password reset token is invalid.');
        }

        return $user;
    }

    /**
     * Return tokens from the repository set in the password broker
     *
     * @return string
     */
    protected function getBrokerToken()
    {
        return $this->broker()->getRepository();
    }

    /**
     * Determine if the passwords are valid for the request.
     *
     * @param  array  $credentials
     * @return bool
     */
    protected function validatePasswordWithDefaults(array $credentials)
    {
        [$password, $confirm] = [
            $credentials['password'],
            $credentials['password_confirmation'],
        ];

        return $password === $confirm && mb_strlen($password) >= 6;
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker()
    {
        return Password::broker();
    }
}
