<?php

uses()->group('service.plan-sync');

use App\Models\Plan;
use App\Models\Price;
use App\Services\PlanSyncService;

test('syncPrice returns an error when no product id is set', function () {
    $price = Price::factory()->create(['stripe_product_id' => null]);

    $result = app(PlanSyncService::class)->syncPrice($price);

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.missing_product');
});

test('sync succeeds when no prices have a product', function () {
    $plan = Plan::factory()->create();
    Price::factory()->for($plan)->create(['stripe_product_id' => null]);

    $result = app(PlanSyncService::class)->sync($plan);

    expect($result->success)->toBeTrue();
});
