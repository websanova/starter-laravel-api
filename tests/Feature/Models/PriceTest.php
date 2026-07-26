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

test('currency defaults to the application currency', function () {
    $plan = Plan::factory()->create();
    $price = $plan->prices()->create(['interval' => PlanInterval::Monthly]);

    expect($price->currency)->toBe(config('cashier.currency'));
});
