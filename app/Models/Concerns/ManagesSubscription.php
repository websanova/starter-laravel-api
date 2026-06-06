<?php

namespace App\Models\Concerns;

use App\Enums\PlanFeature;
use App\Enums\PlanInterval;
use App\Enums\SubscriptionMode;
use App\Models\Plan;
use Laravel\Cashier\Subscription;

trait ManagesSubscription
{
    /**
     * Subscribe to a plan.
     */
    public function subscribeToPlan(Plan $plan, PlanInterval $interval): Subscription
    {
        $subscription = $this->newSubscription('default', $plan->priceId($interval));

        if (config('subscription.mode') === SubscriptionMode::Trial && !$this->subscribed()) {
            $subscription->trialDays(config('subscription.trial_days'));
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
