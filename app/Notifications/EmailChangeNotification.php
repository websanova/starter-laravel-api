<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $token,
        protected string $email
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(config('app.frontend_url') . '/email-reset?token=' . $this->token . '&email=' . urlencode($this->email));

        return (new MailMessage)
            ->subject(__('notifications.email_change.subject'))
            ->line(__('notifications.email_change.line1'))
            ->action(__('notifications.email_change.action'), $url)
            ->line(__('notifications.email_change.line2', ['minutes' => config('auth.email_change.expire', 60)]))
            ->line(__('notifications.email_change.line3'));
    }
}
