<?php

uses()->group('model.plan');

use App\Enums\PlanInterval;
use App\Models\Plan;

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
