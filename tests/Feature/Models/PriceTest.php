<?php

uses()->group('model.price');

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;

test('price belongs to a plan', function () {
    $plan = Plan::factory()->create();
    $price = Price::factory()->for($plan)->create();

    expect($price->plan->id)->toBe($plan->id);
});

test('interval is cast to the enum', function () {
    $price = Price::factory()->create(['interval' => PlanInterval::Monthly]);

    expect($price->interval)->toBe(PlanInterval::Monthly);
});

test('sync returns an error when no product id is set', function () {
    $price = Price::factory()->create(['stripe_product_id' => null]);

    $result = $price->sync();

    expect($result->success)->toBeFalse();
    expect($result->error)->toBe('price.missing_product');
});
