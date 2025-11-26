<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class MyResetPassword extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->view('emails.password-reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
    }
}
