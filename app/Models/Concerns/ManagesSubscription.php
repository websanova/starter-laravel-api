<?php

namespace App\Models\Concerns;

use App\Enums\PlanFeature;
use App\Enums\SubscriptionMode;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;

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
     * Overrides Cashier's own accessor to hand the subscription its owner.
     * Eloquent never fills the inverse side of a hasMany, so every Cashier
     * method that reaches Stripe through $this->owner->stripe() refetches the
     * user we are already holding. That is a wasted query everywhere and a bug
     * in Cashier, it just happens to be fatal here because preventLazyLoading
     * turns it into an error instead of a silent second select.
     *
     * @see https://github.com/laravel/cashier-stripe/issues/1172
     * @see https://github.com/laravel/cashier-stripe/issues/1477
     */
    public function subscription(string $type = 'default'): ?Subscription
    {
        return $this->subscriptions->where('type', $type)->first()?->setRelation('owner', $this);
    }

    /**
     * Recompute the cached plan from the entitlement that actually grants it,
     * which is a live subscription and nothing else, so a lapsed one leaves
     * nothing. Sets the attribute without saving, so the caller decides how and
     * when it gets written. Reads the subscriptions relation and does not load
     * it, so an unloaded caller fails loudly rather than hiding a query per
     * user.
     */
    public function fillPlan(): static
    {
        $this->plan_id = $this->subscribed() ? Plan::forPriceId($this->subscription()?->stripe_price)?->id : null;

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
     * The card on file, or null when there is none. Stripe stamps the brand
     * and last four onto the user during sync, so this reads straight off
     * those columns rather than calling out to the provider.
     */
    protected function paymentMethod(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->pm_type ? [
                'brand' => $this->pm_type,
                'last_four' => $this->pm_last_four,
            ] : null,
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
