<?php

namespace App\Http\Controllers\Api;

use App\Controllers\Features\JWTAuthenticationTrait;
use App\Exceptions\EmailVerificationException;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;


class VerificationController extends Controller
{
    use JWTAuthenticationTrait;

    /**
     * Show the email verification notice.
     *
     * @param $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email has been been verified already.'
            ]);
        }

        return response()->json($request->all());
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$request->hasValidSignature()) {
            throw new EmailVerificationException('The verification link is invalid.');
        }

        // checks if the email is already verified
        if ($user->hasVerifiedEmail()) {
            throw new EmailVerificationException('Email has been been verified already.');
        }

        try {
            // checks if the email can be verified and invoke verified event
            if ($user->markEmailAsVerified()) {
                $user->activate();
                $user->activateMembership();
                $token = auth()->login($user);

                return $this->respondWithToken($token);
            }
        } catch (Exception $e) {
            throw new EmailVerificationException('Email verification failed');
        }
    }

    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resend(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            throw ValidationException::withMessages([
                'email' => 'We can\'t find a user with that e-mail address.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Email has been been verified already.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email has been sent again.'
        ]);
    }
}
