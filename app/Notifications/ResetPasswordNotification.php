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
            ->subject(__('notifications.reset_password.subject'))
            ->line(__('notifications.reset_password.line1'))
            ->action(__('notifications.reset_password.action'), $url)
            ->line(__('notifications.reset_password.line2', ['minutes' => config('auth.passwords.users.expire')]))
            ->line(__('notifications.reset_password.line3'));
    }
}
