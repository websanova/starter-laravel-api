<?php

namespace App\Services\Stripe;

use App\Contracts\PlanSyncProvider;
use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Support\ServiceResult;
use Illuminate\Support\Collection;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;
use Stripe\Price as StripePrice;

class PlanSyncService implements PlanSyncProvider
{
    /**
     * Sync every plan's prices from Stripe.
     */
    public function syncAll(): ServiceResult
    {
        foreach (Plan::all() as $plan) {
            $result = $this->sync($plan);

            if (!$result->success) {
                return $result;
            }
        }

        return ServiceResult::success();
    }

    /**
     * Sync a single plan's prices from Stripe. The plan's lookup keys are
     * fetched in one request, then applied to each price row.
     */
    public function sync(Plan $plan): ServiceResult
    {
        $prices = $plan->prices()->whereNotNull('lookup_key')->get();

        if ($prices->isEmpty()) {
            return ServiceResult::success($plan);
        }

        $result = $this->fetch($prices->pluck('lookup_key')->all());

        if (!$result->success) {
            return $result;
        }

        foreach ($prices as $price) {
            $applied = $this->apply($price, $result->data);

            if (!$applied->success) {
                return $applied;
            }
        }

        return ServiceResult::success($plan);
    }

    /**
     * Sync a single price from Stripe.
     */
    public function syncPrice(Price $price): ServiceResult
    {
        if (!$price->lookup_key) {
            return ServiceResult::error('price.missing_lookup_key');
        }

        $result = $this->fetch([$price->lookup_key]);

        if (!$result->success) {
            return $result;
        }

        return $this->apply($price, $result->data);
    }

    /**
     * Fetch the active Stripe prices for the given lookup keys, keyed by
     * lookup key. Stripe accepts up to ten keys per request, which the
     * unique plan and interval pairing keeps us well under.
     *
     * @param  list<string>  $keys
     */
    protected function fetch(array $keys): ServiceResult
    {
        try {
            $prices = Cashier::stripe()->prices->all([
                'lookup_keys' => $keys,
                'active' => true,
            ]);
        } catch (ApiErrorException) {
            return ServiceResult::error('price.sync_failed');
        }

        return ServiceResult::success(
            Collection::make($prices->data)->keyBy(fn (StripePrice $price) => $price->lookup_key)
        );
    }

    /**
     * Apply the matching Stripe price to a price row.
     *
     * @param  Collection<string, StripePrice>  $stripePrices
     */
    protected function apply(Price $price, Collection $stripePrices): ServiceResult
    {
        $stripePrice = $stripePrices->get($price->lookup_key);

        if (!$stripePrice) {
            return ServiceResult::error('price.not_found');
        }

        if (!$this->intervalMatches($price, $stripePrice)) {
            return ServiceResult::error('price.interval_mismatch');
        }

        $price->update([
            'stripe_price_id' => $stripePrice->id,
            'stripe_product_id' => $stripePrice->product,
            'amount' => $stripePrice->unit_amount ?? 0,
            'currency' => $stripePrice->currency,
        ]);

        return ServiceResult::success($price);
    }

    /**
     * Determine whether the Stripe price bills on the interval the price row
     * claims. Nothing else ties a lookup key to a billing period, so a key
     * moved onto the wrong price would otherwise sync without complaint.
     */
    protected function intervalMatches(Price $price, StripePrice $stripePrice): bool
    {
        $expected = match ($price->interval) {
            PlanInterval::Monthly => 'month',
            PlanInterval::Yearly => 'year',
        };

        return $stripePrice->recurring?->interval === $expected
            && $stripePrice->recurring?->interval_count === 1;
    }
}
