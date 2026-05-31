<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->line('We received a request to reset your password.')
            ->action('Reset Password', $url)
            ->line('This link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
            ->line('If you did not request this, no action is needed.');
    }
}
