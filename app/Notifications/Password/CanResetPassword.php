<?php

namespace App\Notifications\Password;

use App\Notifications\Auth\PasswordResetConfirmationEmail;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Support\Facades\Password;

trait CanResetPassword
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Determine if the user has has a pending password reset token.
     *
     * @return bool
     */
    public function doNotHavePendingToken()
    {
        // @todo: fix it
        return is_null(Password::broker()->getRepository());
    }

    /**
     * Send the successful password reset confirmation email.
     *
     * @return void
     */
    public function sendPasswordResetConfirmationEmail()
    {
        $this->notify(new PasswordResetConfirmationEmail);
    }
}
