<?php

namespace App\Models\Concerns;

use App\Enums\PlanFeature;
use App\Enums\PlanInterval;
use App\Enums\SubscriptionMode;
use App\Models\Plan;
use App\Notifications\PlanCancelledNotification;
use App\Notifications\PlanChangedNotification;
use App\Notifications\PlanResumedNotification;
use App\Notifications\PlanSubscribedNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Cashier\Subscription;
use Laravel\Cashier\SubscriptionBuilder;

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
     * Subscribe to a plan.
     */
    public function subscribeToPlan(Plan $plan, PlanInterval $interval, ?string $promotionCodeId = null): Subscription
    {
        $subscription = $this->newSubscription('default', $plan->priceId($interval));

        $this->applyTrial($subscription);

        if ($promotionCodeId) {
            $subscription->withPromotionCode($promotionCodeId);
        }

        $subscription->create($this->defaultPaymentMethod()?->id);

        $this->load('subscriptions');

        $this->complimentary_plan_id = null;
        $this->fillPlan()->save();

        $this->notify(new PlanSubscribedNotification($plan));

        return $this->subscription();
    }

    /**
     * Swap to a different plan.
     */
    public function swapPlan(Plan $plan, PlanInterval $interval): Subscription
    {
        $this->subscription()->swap($plan->priceId($interval));

        $this->fillPlan()->save();

        $this->notify(new PlanChangedNotification($plan));

        return $this->subscription();
    }

    /**
     * Cancel the subscription at period end.
     */
    public function cancelPlan(): void
    {
        $this->subscription()->cancel();

        $this->notify(new PlanCancelledNotification);
    }

    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function resumePlan(): Subscription
    {
        $this->subscription()->resume();

        $this->notify(new PlanResumedNotification);

        return $this->subscription();
    }

    /**
     * Assign a plan without Stripe billing, cancelling any active subscription.
     */
    public function assignComplimentaryPlan(Plan $plan): void
    {
        $subscription = $this->subscription();

        if ($subscription && !$subscription->ended()) {
            $subscription->cancelNow();
        }

        $this->complimentary_plan_id = $plan->id;
        $this->fillPlan()->save();

        $this->notify(new PlanChangedNotification($plan));
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
     * Whether the user is on a complimentary plan.
     */
    public function onComplimentary(): bool
    {
        return !is_null($this->complimentary_plan_id);
    }

    /**
     * Whether the user is on a complimentary plan.
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
     * Hand the remaining trial to Stripe. An existing date carries over
     * untouched so the clock never resets, an expired one grants nothing, and
     * a first time subscriber who was paywalled from registration starts their
     * trial here. Anyone with a past subscription is returning after a cancel
     * and gets no trial.
     */
    protected function applyTrial(SubscriptionBuilder $subscription): void
    {
        if ($this->trial_ends_at) {
            if ($this->trial_ends_at->isFuture()) {
                $subscription->trialUntil($this->trial_ends_at);
            }

            return;
        }

        if (
            config('subscription.mode') === SubscriptionMode::Trial && 
            config('subscription.require_card_upfront') && 
            $this->subscriptions()->doesntExist()
        ) {
            $subscription->trialDays(config('subscription.trial_days'));
        }
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
