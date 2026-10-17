<?php

uses()->group('app.plan.index');

use App\Enums\SubscriptionMode;

test('only the trial plan is flagged as the trial when the mode is trial', function () {
    config(['subscription.mode' => SubscriptionMode::Trial]);

    $response = $this->getJson('/plans');

    $plans = collect($response->json('data'))->keyBy('slug');

    $response->assertStatus(200);

    expect($plans['pro']['is_trial'])->toBeTrue()
        ->and($plans['free']['is_trial'])->toBeFalse();
});

test('no plan is flagged as the trial when the mode is not trial', function (SubscriptionMode $mode) {
    config(['subscription.mode' => $mode]);

    $response = $this->getJson('/plans');

    $response->assertStatus(200);

    expect(collect($response->json('data'))->pluck('is_trial')->unique()->all())->toBe([false]);
})->with([SubscriptionMode::Freemium, SubscriptionMode::Required]);
