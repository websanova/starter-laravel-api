<?php

uses()->group('public.plan.index');

use App\Models\Plan;

test('guest can list active public plans', function () {
    Plan::factory()->count(2)->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'name', 'slug', 'prices', 'features']],
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

test('response does not expose internal fields', function () {
    Plan::factory()->paid()->create();

    $response = $this->getJson('/plans');

    $response->assertStatus(200)
        ->assertJsonMissingPath('data.0.stripe_monthly_price_id')
        ->assertJsonMissingPath('data.0.stripe_yearly_price_id')
        ->assertJsonMissingPath('data.0.is_active')
        ->assertJsonMissingPath('data.0.is_public');
});
