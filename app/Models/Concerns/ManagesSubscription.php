<?php

namespace App\Models\Concerns;

use App\Enums\PlanFeature;
use App\Enums\SubscriptionMode;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;

trait ManagesSubscription
{
    /**
     * Start the trial clock. The clock starts when access starts, so this only
     * applies when the user is let in without a card. When a card is required
     * up front there is no access to burn until they subscribe.
     */
    public function startTrial(): void
    {
        if (config('subscription.mode') !== SubscriptionMode::Trial || config('subscription.require_card_upfront')) {
            return;
        }

        $this->update(['trial_ends_at' => now()->addDays(config('subscription.trial_days'))]);
    }

    /**
     * Resolve the trial end date handed to Stripe when a subscription is
     * created. An existing stamp decides on its own, carrying over untouched so
     * the clock never resets and granting nothing once it has passed, which is
     * what stops a second trial. Only an unstamped user reaches the fresh
     * window, and only when they were paywalled from registration and have
     * never subscribed before.
     */
    public function resolveTrialEnd(): ?Carbon
    {
        if ($this->trial_ends_at) {
            return $this->trial_ends_at->isFuture() ? $this->trial_ends_at : null;
        }

        if (
            config('subscription.mode') === SubscriptionMode::Trial &&
            config('subscription.require_card_upfront') &&
            $this->subscriptions()->doesntExist()
        ) {
            return now()->addDays(config('subscription.trial_days'));
        }

        return null;
    }

    /**
     * Recompute the cached plan from the entitlement that actually grants it.
     * A complimentary grant wins, otherwise it follows the live subscription,
     * and a lapsed subscription leaves nothing. Sets the attribute without
     * saving so several fills can be chained into one write.
     */
    public function fillPlan(): static
    {
        $this->loadMissing('subscriptions');

        $this->plan_id = $this->complimentary_plan_id
            ?? ($this->subscribed() ? Plan::forPriceId($this->subscription()?->stripe_price)?->id : null);

        return $this;
    }

    /**
     * Get the plan the user is entitled to, falling back to the free plan.
     * Required mode has no free tier to fall back on, so a user without a
     * subscription has no plan at all.
     */
    public function currentPlan(): ?Plan
    {
        if (config('subscription.mode') === SubscriptionMode::Required) {
            return $this->plan;
        }

        return $this->plan ?? Plan::free();
    }

    /**
     * Whether the user holds a complimentary grant. Paired with the
     * is_complimentary attribute below, which serializes the same check for
     * API responses. This method form is the one to use in guards.
     */
    public function onComplimentary(): bool
    {
        return !is_null($this->complimentary_plan_id);
    }

    /**
     * Whether the user holds a complimentary grant, in attribute form so it
     * serializes into responses alongside is_subscribed and is_on_trial.
     * Same check as onComplimentary().
     */
    protected function isComplimentary(): Attribute
    {
        return Attribute::make(
            get: fn () => !is_null($this->complimentary_plan_id),
        );
    }

    /**
     * Whether the user has an active subscription.
     */
    protected function isSubscribed(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->ensureSubscriptionsLoaded();

                return $this->subscribed();
            },
        );
    }

    /**
     * Whether the user is currently on a trial.
     */
    protected function isOnTrial(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->ensureSubscriptionsLoaded();

                return $this->onTrial();
            },
        );
    }

    /**
     * Whether the user has cancelled but is still within the grace period.
     */
    protected function isOnGracePeriod(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->ensureSubscriptionsLoaded();

                $subscription = $this->subscription();

                return $subscription && $subscription->onGracePeriod();
            },
        );
    }

    /**
     * Ensure the subscriptions relation is eager loaded.
     *
     * @throws \LogicException
     */
    protected function ensureSubscriptionsLoaded(): void
    {
        if (!$this->relationLoaded('subscriptions')) {
            throw new \LogicException('The subscriptions relation must be eager loaded before accessing subscription status attributes.');
        }
    }

    /**
     * Check if the user can use a plan feature.
     * For countable features, checks the relationship count against the limit.
     * For boolean features, checks if the feature is enabled.
     */
    public function canUsePlanFeature(PlanFeature $feature): bool
    {
        $plan = $this->currentPlan();

        if (!$plan) {
            return false;
        }

        $value = $plan->feature($feature->value);

        if ($feature->isCountable()) {
            if (is_null($value)) {
                return true;
            }

            return $this->{$feature->relation()}()->count() < $value;
        }

        return (bool) $value;
    }
}
