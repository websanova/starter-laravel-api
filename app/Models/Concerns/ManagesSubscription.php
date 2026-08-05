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
     * Resolve the plan the user is entitled to from the entitlement that
     * actually grants it. A live subscription wins, a complimentary grant
     * covers the rest, and a lapsed subscription with no grant behind it leaves
     * nothing. Hands back the id without touching the model, so the caller
     * decides how and when it gets written. Reads the subscriptions relation
     * and does not load it, so an unloaded caller fails loudly rather than
     * hiding a query per user.
     */
    public function resolvePlanId(): ?int
    {
        return ($this->subscribed() ? Plan::forPriceId($this->subscription()?->stripe_price)?->id : null)
            ?? $this->complimentary_plan_id;
    }

    /**
     * Write the given plan id, reporting whether this call is the one that
     * moved it. Stripe repeats subscription events and does not order
     * deliveries, so two can be in here at once. Reading, comparing, then
     * writing would let both conclude they moved it and both mail the user, so
     * the comparison is made part of the write and decided under the row lock.
     */
    public function claimPlan(?int $planId): bool
    {
        /**
         * Cleared alongside the claim rather than folded into it, since it is a
         * column with its own lifetime. Resolution already ignores it once a
         * subscription is live, but is_complimentary and the stats queries read
         * the column straight, so a grant left behind would keep reporting a
         * user as complimentary after they started paying for the same plan.
         */
        if ($this->complimentary_plan_id && $this->subscription()?->valid()) {
            $this->update(['complimentary_plan_id' => null]);
        }

        /**
         * The affected row count cannot decide this on its own, since MySQL
         * counts the rows it changed while SQLite counts the rows it matched.
         * Writing the same plan back reports zero on the first and one on the
         * second, so the where clause has to make the decision instead. The
         * null arm is the same test written the only way it can be, since no
         * operator compares a column to null.
         */
        return (bool) static::whereKey($this->id)
            ->where(fn ($query) => $planId
                ? $query->whereNull('plan_id')->orWhere('plan_id', '!=', $planId)
                : $query->whereNotNull('plan_id'))
            ->update(['plan_id' => $planId]);
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
