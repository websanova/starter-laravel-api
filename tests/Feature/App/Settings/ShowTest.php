<?php

uses()->group('app.settings.show');

test('guest can retrieve settings', function () {
    $response = $this->getJson('/settings');

    $response->assertStatus(200)
        ->assertExactJson([
            'data' => [
                'subscription_automatic_tax' => config('subscription.automatic_tax'),
                'subscription_card_upfront' => config('subscription.require_card_upfront'),
                'subscription_mode' => config('subscription.mode')->value,
                'subscription_trial_days' => config('subscription.trial_days'),
                'verification_code_length' => config('verification.code_length'),
                'verification_required' => ['email'],
            ],
        ]);
});

test('settings are available on the admin path', function () {
    $response = $this->getJson('/admin/settings');

    $response->assertStatus(200)
        ->assertExactJson([
            'data' => [
                'subscription_automatic_tax' => config('subscription.automatic_tax'),
                'subscription_card_upfront' => config('subscription.require_card_upfront'),
                'subscription_mode' => config('subscription.mode')->value,
                'subscription_trial_days' => config('subscription.trial_days'),
                'verification_code_length' => config('verification.code_length'),
                'verification_required' => ['email'],
            ],
        ]);
});
