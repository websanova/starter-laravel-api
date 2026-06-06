<?php

uses()->group('model.plan');

use App\Enums\PlanInterval;
use App\Models\Plan;

test('cached returns all plans ordered by sort_order', function () {
    Plan::factory()->create(['name' => 'Pro', 'sort_order' => 2]);
    Plan::factory()->create(['name' => 'Free', 'sort_order' => 0]);
    Plan::factory()->create(['name' => 'Business', 'sort_order' => 1]);

    $plans = Plan::cached();

    expect($plans)->toHaveCount(3);
    expect($plans[0]->name)->toBe('Free');
    expect($plans[1]->name)->toBe('Business');
    expect($plans[2]->name)->toBe('Pro');
});

test('cached is invalidated when a plan is saved', function () {
    $plan = Plan::factory()->create(['name' => 'Original']);

    $cached = Plan::cached();
    expect($cached->first()->name)->toBe('Original');

    $plan->update(['name' => 'Updated']);

    $cached = Plan::cached();
    expect($cached->first()->name)->toBe('Updated');
});

test('cached is invalidated when a plan is deleted', function () {
    Plan::factory()->count(2)->create();

    expect(Plan::cached())->toHaveCount(2);

    Plan::first()->delete();

    expect(Plan::cached())->toHaveCount(1);
});

test('free returns the free plan', function () {
    Plan::factory()->create(['slug' => 'pro']);
    Plan::factory()->create(['slug' => 'free']);

    $free = Plan::free();

    expect($free)->not->toBeNull();
    expect($free->slug)->toBe('free');
});

test('free returns null when no free plan exists', function () {
    Plan::factory()->create(['slug' => 'pro']);

    expect(Plan::free())->toBeNull();
});

test('priceId returns the correct stripe price id', function () {
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id' => 'price_monthly_abc',
        'stripe_yearly_price_id' => 'price_yearly_abc',
    ]);

    expect($plan->priceId(PlanInterval::Monthly))->toBe('price_monthly_abc');
    expect($plan->priceId(PlanInterval::Yearly))->toBe('price_yearly_abc');
});

test('isFree returns true when no stripe prices are set', function () {
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id' => null,
        'stripe_yearly_price_id' => null,
    ]);

    expect($plan->isFree())->toBeTrue();
});

test('isFree returns false when stripe prices are set', function () {
    $plan = Plan::factory()->paid()->create();

    expect($plan->isFree())->toBeFalse();
});

test('feature returns value for existing feature', function () {
    $plan = Plan::factory()->create(['features' => ['bookmarks' => 10]]);

    expect($plan->feature('bookmarks'))->toBe(10);
});

test('feature returns default for missing feature', function () {
    $plan = Plan::factory()->create(['features' => []]);

    expect($plan->feature('bookmarks'))->toBeNull();
    expect($plan->feature('bookmarks', 5))->toBe(5);
});
