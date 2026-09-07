<?php

namespace App\Listeners\Stripe;

use App\Models\Plan;
use App\Notifications\PlanCancelledNotification;
use App\Notifications\PlanChangedNotification;
use App\Notifications\PlanResumedNotification;
use App\Notifications\PlanSubscribedNotification;
use Illuminate\Notifications\Notification;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

class CommitPlan
{
    /**
     * Commit the plan the user is entitled to and announce every move Stripe
     * reports. Portal swaps, dunning and payments that fail their way to
     * cancelled never touch our own write paths, so the webhook is the only
     * signal for any of it.
     *
     * The delete is handled for the write alone. Cancelling runs to the period
     * end, so the update carrying cancel_at_period_end announced it already
     * while the plan itself is held through the grace period, and the delete is
     * what finally drops it. An immediate cancel is the one flow that emits a
     * delete without a prior update, and this API exposes no route to it.
     */
    public function handle(WebhookHandled $event): void
    {
        $events = [
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ];

        if (!in_array($event->payload['type'], $events)) {
            return;
        }

        $data = $event->payload['data']['object'];

        $user = Cashier::findBillable($data['customer']);

        if (!$user) {
            return;
        }

        /**
         * Cashier's own handler wrote the subscription row moments ago, so this
         * is the one entry point where the relation has to be pulled fresh
         * rather than taken as given. The plan is then resolved off the live
         * subscription instead of the payload price, which is what holds a
         * cancelled user on their plan until the grace period runs out.
         */
        $user->load('subscriptions');

        $user->fillPlan()->save();

        $notification = match ($event->payload['type']) {
            'customer.subscription.created' => $this->created($data),
            'customer.subscription.updated' => $this->updated($data, $event->payload['data']['previous_attributes'] ?? []),
            default => null,
        };

        if ($notification) {
            $user->notify($notification);
        }
    }

    /**
     * A new subscription only counts once it grants something. Stripe creates
     * one the moment the card is submitted, so announcing every create would
     * announce plans to users whose payment never cleared. The ones that start
     * out incomplete are picked up by their activating update instead.
     */
    protected function created(array $data): ?Notification
    {
        if (!$this->isLive($data['status'])) {
            return null;
        }

        $plan = $this->plan($data);

        return $plan ? new PlanSubscribedNotification($plan) : null;
    }

    /**
     * Stripe sends an update for renewals, card changes and status flips as
     * well as for the moves worth announcing, so previous_attributes decides.
     * It carries only the fields that actually changed, and their old values.
     */
    protected function updated(array $data, array $previous): ?Notification
    {
        if (array_key_exists('cancel_at_period_end', $previous)) {
            return $data['cancel_at_period_end']
                ? new PlanCancelledNotification
                : new PlanResumedNotification;
        }

        $plan = $this->plan($data);

        if (!$plan) {
            return null;
        }

        /**
         * The signup path lands here rather than on the create. A card that
         * needs confirming leaves the subscription incomplete, and it is this
         * update that reports the charge clearing, so the first announcement a
         * paying user gets is the one made when the status turns live.
         */
        if (array_key_exists('status', $previous) && $this->isLive($data['status']) && !$this->isLive($previous['status'])) {
            return new PlanSubscribedNotification($plan);
        }

        if (!array_key_exists('items', $previous)) {
            return null;
        }

        $fromPriceId = $previous['items']['data'][0]['price']['id'] ?? null;

        return new PlanChangedNotification(
            $plan,
            Plan::intervalForPriceId($this->priceId($data)),
            Plan::forPriceId($fromPriceId),
            Plan::intervalForPriceId($fromPriceId),
        );
    }

    /**
     * Whether the status is one that actually grants the plan.
     */
    protected function isLive(?string $status): bool
    {
        return in_array($status, ['active', 'trialing']);
    }

    /**
     * Resolve the plan from the price the subscription now carries.
     */
    protected function plan(array $data): ?Plan
    {
        return Plan::forPriceId($this->priceId($data));
    }

    /**
     * The Stripe price the subscription now carries.
     */
    protected function priceId(array $data): ?string
    {
        return $data['items']['data'][0]['price']['id'] ?? null;
    }
}
