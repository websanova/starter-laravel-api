<?php

uses()->group('model.user.subscription');

use App\Enums\PlanFeature;
use App\Enums\SubscriptionMode;
use App\Models\Plan;
use App\Models\User;

test('user without plan falls back to free plan', function () {
    $user = User::factory()->create(['plan_id' => null]);

    expect($user->currentPlan()->slug)->toBe('free');
    expect($user->currentPlan()->name)->toBe('Free');
});

test('user with plan returns their assigned plan', function () {
    $pro = Plan::where('slug', 'pro')->first();

    $user = User::factory()->create(['plan_id' => $pro->id]);

    expect($user->plan->slug)->toBe('pro');
});

test('canUsePlanFeature checks count for countable features', function () {
    $plan = Plan::factory()->create(['features' => ['bookmarks' => 2]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    expect($user->canUsePlanFeature(PlanFeature::Bookmarks))->toBeTrue();

    \App\Models\Bookmark::factory()->count(2)->create(['user_id' => $user->id]);

    // Refresh to clear any cached counts
    $user = $user->fresh();
    expect($user->canUsePlanFeature(PlanFeature::Bookmarks))->toBeFalse();
});

test('canUsePlanFeature returns true for null limit (unlimited)', function () {
    $plan = Plan::factory()->create(['features' => ['bookmarks' => null]]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    \App\Models\Bookmark::factory()->count(100)->create(['user_id' => $user->id]);

    expect($user->canUsePlanFeature(PlanFeature::Bookmarks))->toBeTrue();
});

test('canUsePlanFeature returns true for undefined feature (no limit)', function () {
    $plan = Plan::factory()->create(['features' => []]);
    $user = User::factory()->create(['plan_id' => $plan->id]);

    expect($user->canUsePlanFeature(PlanFeature::Bookmarks))->toBeTrue();
});

test('is_subscribed throws LogicException when subscriptions not loaded', function () {
    $user = User::factory()->create();

    expect(fn () => $user->is_subscribed)
        ->toThrow(\LogicException::class);
});

test('is_on_trial throws LogicException when subscriptions not loaded', function () {
    $user = User::factory()->create();

    expect(fn () => $user->is_on_trial)
        ->toThrow(\LogicException::class);
});

test('is_on_grace_period throws LogicException when subscriptions not loaded', function () {
    $user = User::factory()->create();

    expect(fn () => $user->is_on_grace_period)
        ->toThrow(\LogicException::class);
});

test('is_subscribed returns false when subscriptions loaded but none exist', function () {
    $user = User::factory()->create();
    $user->load('subscriptions');

    expect($user->is_subscribed)->toBeFalse();
});

test('is_on_trial returns false when subscriptions loaded but no trial', function () {
    $user = User::factory()->create(['trial_ends_at' => null]);
    $user->load('subscriptions');

    expect($user->is_on_trial)->toBeFalse();
});

test('is_on_trial returns true when user has generic trial', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(7)]);
    $user->load('subscriptions');

    expect($user->is_on_trial)->toBeTrue();
});

test('is_on_grace_period returns false when subscriptions loaded but none exist', function () {
    $user = User::factory()->create();
    $user->load('subscriptions');

    expect($user->is_on_grace_period)->toBeFalse();
});

test('trial plan skips the free plan', function () {
    expect(Plan::trial()->slug)->toBe('pro');
});

test('trial plan is the lowest tier that is not free', function () {
    Plan::where('slug', 'pro')->first()->update(['tier' => 5]);

    $lower = Plan::factory()->create(['tier' => 2]);

    expect(Plan::trial()->id)->toBe($lower->id);
});

test('resolveTrialEnd carries a remaining trial onto the trial plan', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(5)]);

    expect($user->resolveTrialEnd(Plan::trial())->timestamp)->toBe($user->trial_ends_at->timestamp);
});

test('resolveTrialEnd gives nothing on a plan other than the trial plan', function () {
    $user = User::factory()->create(['trial_ends_at' => now()->addDays(5)]);

    $other = Plan::factory()->create(['tier' => Plan::trial()->tier + 1]);

    expect($user->resolveTrialEnd($other))->toBeNull();
});

test('resolveTrialEnd starts a fresh window for a first time subscriber paywalled from registration', function () {
    $this->freezeTime();

    config([
        'subscription.mode' => SubscriptionMode::Trial,
        'subscription.require_card_upfront' => true,
        'subscription.trial_days' => 14,
    ]);

    $user = User::factory()->create(['trial_ends_at' => null]);

    expect($user->resolveTrialEnd(Plan::trial())->timestamp)->toBe(now()->addDays(14)->timestamp);
});
