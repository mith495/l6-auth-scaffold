<?php

namespace App\Listeners\Auth;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetConfirmationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->user instanceof CanResetPassword) {
            $event->user->sendPasswordResetConfirmationEmail();
        }
    }
}
