<?php

uses()->group('resource.app.profile');

use App\Http\Resources\App\ProfileResource;
use App\Models\Plan;
use App\Models\User;

test('subscription fields always included', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);
    $user->load(['plan.prices', 'subscriptions']);

    $resource = (new ProfileResource($user))->toArray(request());

    expect($resource)->toHaveKeys(['is_subscribed', 'is_on_trial', 'is_on_grace_period']);
});

test('subscription fields show false when user has no subscriptions', function () {
    $user = User::factory()->create();
    $user->load(['plan.prices', 'subscriptions']);

    $resource = (new ProfileResource($user))->toArray(request());

    expect($resource['is_subscribed'])->toBeFalse();
    expect($resource['is_on_trial'])->toBeFalse();
    expect($resource['is_on_grace_period'])->toBeFalse();
});

test('plan data included when user has a plan', function () {
    $plan = Plan::where('slug', 'pro')->first();
    $plan->update(['features' => ['bookmarks' => 100]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);
    $user->load(['plan.prices', 'subscriptions']);

    $resource = (new ProfileResource($user))->toArray(request());

    expect($resource['plan'])->toHaveKeys(['id', 'name', 'slug', 'features']);
    expect($resource['plan']['slug'])->toBe('pro');
    expect($resource['plan']['features'])->toBe(['bookmarks' => 100]);
});

test('locale and timezone returned as stored when set', function () {
    $user = User::factory()->create(['locale' => 'en', 'timezone' => 'Europe/London']);
    $user->load(['plan.prices', 'subscriptions']);

    $resource = (new ProfileResource($user))->toArray(request());

    expect($resource['locale'])->toBe('en');
    expect($resource['timezone'])->toBe('Europe/London');
});

test('locale and timezone fall back to config defaults when null', function () {
    $user = User::factory()->create(['locale' => null, 'timezone' => null]);
    $user->load(['plan.prices', 'subscriptions']);

    $resource = (new ProfileResource($user))->toArray(request());

    expect($resource['locale'])->toBe(config('auth.user.default_locale'));
    expect($resource['timezone'])->toBe(config('auth.user.default_timezone'));
});

test('trial_ends_at always included', function () {
    $trialEnd = now()->addDays(7);
    $user = User::factory()->create(['trial_ends_at' => $trialEnd]);
    $user->load(['plan.prices', 'subscriptions']);

    $resource = (new ProfileResource($user))->toArray(request());

    expect($resource)->toHaveKey('trial_ends_at');
    expect($resource['trial_ends_at']->toDateTimeString())->toBe($trialEnd->toDateTimeString());
});

