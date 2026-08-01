<?php

uses()->group('service.plan-sync');

use App\Models\Plan;
use App\Models\Price;
use App\Contracts\PlanSyncProvider;

test('syncPrice returns an error when no product id is set', function () {
    $price = Price::factory()->create(['stripe_product_id' => null]);

    $result = app(PlanSyncProvider::class)->syncPrice($price);

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.missing_product');
});

test('sync succeeds when no prices have a product', function () {
    $plan = Plan::factory()->create();
    Price::factory()->for($plan)->create(['stripe_product_id' => null]);

    $result = app(PlanSyncProvider::class)->sync($plan);

    expect($result->success)->toBeTrue();
});

test('syncPrice populates the price id, amount and currency from the stripe product', function () {
    $productId = config('subscription.stripe_products.pro.monthly');

    if (!$productId) {
        $this->markTestSkipped('Stripe products not configured.');
    }

    $price = Price::factory()->create([
        'stripe_product_id' => $productId,
        'stripe_price_id' => null,
        'amount' => 0,
    ]);

    $result = app(PlanSyncProvider::class)->syncPrice($price);

    expect($result->success)->toBeTrue();
    expect($price->fresh()->stripe_price_id)->not->toBeNull();
    expect($price->fresh()->amount)->toBeGreaterThan(0);
    expect($price->fresh()->currency)->not->toBeEmpty();
});

test('syncPrice returns an error when the stripe product does not exist', function () {
    if (!config('subscription.stripe_products.pro.monthly')) {
        $this->markTestSkipped('Stripe products not configured.');
    }

    $price = Price::factory()->create(['stripe_product_id' => 'prod_nonexistent0000']);

    $result = app(PlanSyncProvider::class)->syncPrice($price);

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.sync_failed');
});
