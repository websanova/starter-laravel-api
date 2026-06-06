<?php

uses()->group('model.user.resource');

use App\Http\Resources\Account\UserResource;
use App\Models\Plan;
use App\Models\User;

test('subscription fields included when subscriptions relation loaded', function () {
    $user = User::factory()->create(['plan_id' => null, 'trial_ends_at' => null]);
    $user->load('subscriptions');

    $resource = (new UserResource($user))->toArray(request());

    expect($resource)->toHaveKeys(['is_subscribed', 'is_on_trial', 'is_on_grace_period']);
});

test('subscription fields omitted when subscriptions relation not loaded', function () {
    $user = User::factory()->create(['plan_id' => null]);

    $resource = (new UserResource($user))->toArray(request());

    expect($resource)->not->toHaveKey('is_subscribed');
    expect($resource)->not->toHaveKey('is_on_trial');
    expect($resource)->not->toHaveKey('is_on_grace_period');
});

test('plan data always included in user resource', function () {
    $plan = Plan::where('slug', 'pro')->first();
    $plan->update(['features' => ['bookmarks' => 100]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $resource = (new UserResource($user))->toArray(request());

    expect($resource['plan'])->toHaveKeys(['id', 'name', 'slug', 'features']);
    expect($resource['plan']['slug'])->toBe('pro');
    expect($resource['plan']['features'])->toBe(['bookmarks' => 100]);
});

test('trial_ends_at always included in user resource', function () {
    $trialEnd = now()->addDays(7);
    $user = User::factory()->create(['plan_id' => null, 'trial_ends_at' => $trialEnd]);

    $resource = (new UserResource($user))->toArray(request());

    expect($resource)->toHaveKey('trial_ends_at');
    expect($resource['trial_ends_at']->toDateTimeString())->toBe($trialEnd->toDateTimeString());
});
