<?php

uses()->group('app.plan.index');

use App\Models\Plan;

test('guest can list active public plans', function () {
    Plan::factory()->count(2)->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'name', 'slug', 'prices', 'features', 'tier']],
        ])
        ->assertJsonCount(4, 'data');
});

test('inactive plans are excluded', function () {
    Plan::factory()->create();
    Plan::factory()->inactive()->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('private plans are excluded', function () {
    Plan::factory()->create();
    Plan::factory()->private()->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('prices are keyed by interval with amount and currency', function () {
    $plan = Plan::factory()->paid()->create();

    $response = $this->getJson('/plans');

    $prices = collect($response->json('data'))->firstWhere('slug', $plan->slug)['prices'];

    $response->assertStatus(200);
    expect($prices['monthly'])->toBe(['amount' => 999, 'currency' => config('cashier.currency')]);
    expect($prices['yearly'])->toBe(['amount' => 9990, 'currency' => config('cashier.currency')]);
});

test('a plan with no prices serializes prices as an object', function () {
    Plan::factory()->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200);
    expect($response->getContent())->toContain('"prices":{}');
});

test('response does not expose internal fields', function () {
    Plan::factory()->paid()->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200)
        ->assertJsonMissingPath('data.0.stripe_monthly_price_id')
        ->assertJsonMissingPath('data.0.stripe_yearly_price_id')
        ->assertJsonMissingPath('data.0.is_active')
        ->assertJsonMissingPath('data.0.is_public');
});

