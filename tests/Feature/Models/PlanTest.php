<?php

uses()->group('model.plan');

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;

test('cached returns all plans ordered by sort_order', function () {
    Plan::factory()->create(['name' => 'Gamma', 'sort_order' => 20]);
    Plan::factory()->create(['name' => 'Alpha', 'sort_order' => 10]);

    $plans = Plan::cached();
    $names = $plans->pluck('name');

    $alphaIndex = $names->search('Alpha');
    $gammaIndex = $names->search('Gamma');

    expect($alphaIndex)->toBeLessThan($gammaIndex);
});

test('cached is invalidated when a plan is saved', function () {
    $plan = Plan::factory()->create(['name' => 'Original', 'sort_order' => 99]);

    $cached = Plan::cached();
    expect($cached->last()->name)->toBe('Original');

    $plan->update(['name' => 'Updated']);

    $cached = Plan::cached();
    expect($cached->last()->name)->toBe('Updated');
});

test('cached is invalidated when a plan is deleted', function () {
    $initialCount = Plan::count();
    Plan::factory()->create();

    expect(Plan::cached())->toHaveCount($initialCount + 1);

    Plan::orderBy('id', 'desc')->first()->delete();

    expect(Plan::cached())->toHaveCount($initialCount);
});

test('free returns the free plan', function () {
    $free = Plan::free();

    expect($free)->not->toBeNull();
    expect($free->slug)->toBe('free');
});

test('free returns null when no free plan exists', function () {
    Plan::where('slug', 'free')->delete();

    expect(Plan::free())->toBeNull();
});

test('priceId returns the correct stripe price id', function () {
    $plan = Plan::factory()->create();

    Price::factory()->for($plan)->create([
        'interval' => PlanInterval::Monthly,
        'stripe_price_id' => 'price_monthly_abc',
    ]);
    Price::factory()->for($plan)->create([
        'interval' => PlanInterval::Yearly,
        'stripe_price_id' => 'price_yearly_abc',
    ]);

    expect($plan->priceId(PlanInterval::Monthly))->toBe('price_monthly_abc');
    expect($plan->priceId(PlanInterval::Yearly))->toBe('price_yearly_abc');
});

test('is_billable returns false when no stripe prices are set', function () {
    $plan = Plan::factory()->create();

    expect($plan->is_billable)->toBeFalse();
});

test('is_billable returns true when stripe prices are set', function () {
    $plan = Plan::factory()->paid()->create();

    expect($plan->is_billable)->toBeTrue();
});

test('sync succeeds when no prices have a product', function () {
    $plan = Plan::factory()->create();
    Price::factory()->for($plan)->create(['stripe_product_id' => null]);

    $result = $plan->sync();

    expect($result->success)->toBeTrue();
});

test('is_complimentary returns true for non-free plan with no prices', function () {
    $plan = Plan::factory()->create(['slug' => 'pro-comp']);

    expect($plan->is_complimentary)->toBeTrue();
});

test('is_complimentary returns false for free plan', function () {
    $plan = Plan::free();

    expect($plan->is_complimentary)->toBeFalse();
});

test('is_complimentary returns false for paid plan', function () {
    $plan = Plan::factory()->paid()->create();

    expect($plan->is_complimentary)->toBeFalse();
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
