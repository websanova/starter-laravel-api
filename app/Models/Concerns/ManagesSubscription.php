<?php

namespace App\Models\Concerns;

use App\Enums\PlanFeature;
use App\Enums\PlanInterval;
use App\Enums\SubscriptionMode;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Cashier\Subscription;

trait ManagesSubscription
{
    /**
     * Subscribe to a plan.
     */
    public function subscribeToPlan(Plan $plan, PlanInterval $interval, ?string $promotionCodeId = null): Subscription
    {
        $subscription = $this->newSubscription('default', $plan->priceId($interval));

        if (config('subscription.mode') === SubscriptionMode::Trial && !$this->subscribed()) {
            $subscription->trialDays(config('subscription.trial_days'));
        }

        if ($promotionCodeId) {
            $subscription->withPromotionCode($promotionCodeId);
        }

        $subscription->create($this->defaultPaymentMethod()?->id);

        $this->update(['plan_id' => $plan->id]);

        return $this->subscription();
    }

    /**
     * Swap to a different plan.
     */
    public function swapPlan(Plan $plan, PlanInterval $interval): Subscription
    {
        $this->subscription()->swap($plan->priceId($interval));
        $this->update(['plan_id' => $plan->id]);

        return $this->subscription();
    }

    /**
     * Cancel the subscription at period end.
     */
    public function cancelPlan(): void
    {
        $this->subscription()->cancel();
    }

    /**
     * Resume a cancelled subscription before the period ends.
     */
    public function resumePlan(): Subscription
    {
        $this->subscription()->resume();

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

        $this->update(['plan_id' => $plan->id]);
    }

    /**
     * Whether the user is on a complimentary plan.
     */
    public function onComplimentary(): bool
    {
        return $this->plan->is_complimentary;
    }

    /**
     * Whether the user is on a complimentary plan.
     */
    protected function isComplimentary(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->plan->is_complimentary,
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
        $value = $this->plan->feature($feature->value);

        if ($feature->isCountable()) {
            if (is_null($value)) {
                return true;
            }

            return $this->{$feature->relation()}()->count() < $value;
        }

        return (bool) $value;
    }
}
