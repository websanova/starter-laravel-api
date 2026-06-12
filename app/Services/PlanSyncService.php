<?php

namespace App\Services;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Support\ServiceResult;
use Laravel\Cashier\Cashier;
use Stripe\Exception\ApiErrorException;

class PlanSyncService
{
    /**
     * Sync every plan's prices from their Stripe product default prices.
     */
    public function syncAll(?PlanInterval $interval = null): ServiceResult
    {
        foreach (Plan::all() as $plan) {
            $result = $this->sync($plan, $interval);

            if (!$result->success) {
                return $result;
            }
        }

        return ServiceResult::success();
    }

    /**
     * Sync a single plan's prices, optionally limited to one interval.
     */
    public function sync(Plan $plan, ?PlanInterval $interval = null): ServiceResult
    {
        $prices = $plan->prices()
            ->whereNotNull('stripe_product_id')
            ->when($interval, fn ($query) => $query->where('interval', $interval->value))
            ->get();

        foreach ($prices as $price) {
            $result = $this->syncPrice($price);

            if (!$result->success) {
                return $result;
            }
        }

        return ServiceResult::success($plan);
    }

    /**
     * Sync a single price's Stripe price ID and amount from its product's default price.
     */
    public function syncPrice(Price $price): ServiceResult
    {
        if (!$price->stripe_product_id) {
            return ServiceResult::error('price.missing_product');
        }

        try {
            $product = Cashier::stripe()->products->retrieve(
                $price->stripe_product_id,
                ['expand' => ['default_price']],
            );
        } catch (ApiErrorException) {
            return ServiceResult::error('price.sync_failed');
        }

        $default = $product->default_price;

        if (!$default) {
            return ServiceResult::error('price.no_default_price');
        }

        $price->update([
            'stripe_price_id' => $default->id,
            'amount' => $default->unit_amount ?? 0,
        ]);

        return ServiceResult::success($price);
    }
}
