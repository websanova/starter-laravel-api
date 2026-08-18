<?php

uses()->group('service.plan-sync');

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Contracts\SyncPlanPricesProvider;

test('handlePrice returns an error when no lookup key is set', function () {
    $price = Price::factory()->create(['lookup_key' => null]);

    $result = app(SyncPlanPricesProvider::class)->handlePrice($price);

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.missing_lookup_key');
});

test('handle succeeds when no prices have a lookup key', function () {
    $plan = Plan::factory()->create();
    Price::factory()->for($plan)->create(['lookup_key' => null]);

    $result = app(SyncPlanPricesProvider::class)->handle($plan);

    expect($result->success)->toBeTrue();
});

test('handlePrice populates the price id, product id, amount and currency from stripe', function () {
    if (!config('cashier.secret')) {
        $this->markTestSkipped('Stripe is not configured.');
    }

    // The plans migration seeds the Pro prices, so these lookup keys are taken.
    Price::query()->delete();

    $price = Price::factory()->create([
        'lookup_key' => 'pro_monthly',
        'interval' => PlanInterval::Monthly,
        'stripe_price_id' => null,
        'stripe_product_id' => null,
        'amount' => 0,
    ]);

    $result = app(SyncPlanPricesProvider::class)->handlePrice($price);

    expect($result->success)->toBeTrue();
    expect($price->fresh()->stripe_price_id)->not->toBeNull();
    expect($price->fresh()->stripe_product_id)->not->toBeNull();
    expect($price->fresh()->amount)->toBeGreaterThan(0);
    expect($price->fresh()->currency)->not->toBeEmpty();
});

test('handlePrice returns an error when no active stripe price matches the lookup key', function () {
    if (!config('cashier.secret')) {
        $this->markTestSkipped('Stripe is not configured.');
    }

    $price = Price::factory()->create(['lookup_key' => 'nonexistent_lookup_key']);

    $result = app(SyncPlanPricesProvider::class)->handlePrice($price);

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.not_found');
});

test('handlePrice returns an error when the stripe price bills on a different interval', function () {
    if (!config('cashier.secret')) {
        $this->markTestSkipped('Stripe is not configured.');
    }

    // The plans migration seeds the Pro prices, so these lookup keys are taken.
    Price::query()->delete();

    $price = Price::factory()->create([
        'lookup_key' => 'pro_monthly',
        'interval' => PlanInterval::Yearly,
    ]);

    $result = app(SyncPlanPricesProvider::class)->handlePrice($price);

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.interval_mismatch');
});

test('handle populates every price on the plan in one pass', function () {
    if (!config('cashier.secret')) {
        $this->markTestSkipped('Stripe is not configured.');
    }

    // The plans migration seeds the Pro prices, so these lookup keys are taken.
    Price::query()->delete();

    $plan = Plan::factory()->create();

    $plan->prices()->create([
        'lookup_key' => 'pro_monthly',
        'interval' => PlanInterval::Monthly,
    ]);

    $plan->prices()->create([
        'lookup_key' => 'pro_yearly',
        'interval' => PlanInterval::Yearly,
    ]);

    $result = app(SyncPlanPricesProvider::class)->handle($plan);

    expect($result->success)->toBeTrue();
    expect($plan->prices()->whereNull('stripe_price_id')->count())->toBe(0);
});
