<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $code
    ) {}

    /**
     * Map config channel names to Laravel notification channels.
     */
    protected array $channelMap = [
        'email' => 'mail',
        'sms' => 'vonage',
    ];

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return collect(config('verification.channels'))
            ->map(fn (string $channel) => $this->channelMap[$channel] ?? $channel)
            ->all();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.verification.subject'))
            ->line(__('notifications.verification.line1'))
            ->line($this->code)
            ->line(__('notifications.verification.line2', ['minutes' => (int) (config('verification.code_expiry') / 60)]));
    }
}
