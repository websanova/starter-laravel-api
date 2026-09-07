<?php

namespace App\Notifications;

use App\Enums\PlanInterval;
use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlanChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance. The plan and interval moved from are
     * optional, since a webhook that reports the change without carrying the
     * previous price leaves nothing to name on that side.
     */
    public function __construct(
        protected Plan $plan,
        protected ?PlanInterval $interval = null,
        protected ?Plan $fromPlan = null,
        protected ?PlanInterval $fromInterval = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.plan_changed.subject'))
            ->line($this->body());
    }

    /**
     * Get the database representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('notifications.plan_changed.subject'),
            'body' => $this->body(),
        ];
    }

    /**
     * The line naming the move, falling back to the plan alone when there is no
     * previous plan and interval to name.
     */
    protected function body(): string
    {
        if (!$this->fromPlan || !$this->fromInterval || !$this->interval) {
            return __('notifications.plan_changed.line1', ['plan' => $this->plan->display_name]);
        }

        return __('notifications.plan_changed.line1_from', [
            'from' => $this->label($this->fromPlan, $this->fromInterval),
            'to' => $this->label($this->plan, $this->interval),
        ]);
    }

    /**
     * Name a plan and the interval it bills on.
     */
    protected function label(Plan $plan, PlanInterval $interval): string
    {
        return $plan->display_name . ' (' . $interval->displayName() . ')';
    }
}
