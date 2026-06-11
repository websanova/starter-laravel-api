<?php

uses()->group('console.plans-sync');

use App\Models\Plan;
use App\Models\Price;

test('command runs without error when no products are configured', function () {
    $this->artisan('plans:sync')->assertExitCode(0);
});

test('command skips prices that have no product', function () {
    $plan = Plan::factory()->create();
    Price::factory()->for($plan)->create(['stripe_product_id' => null]);

    $this->artisan('plans:sync')
        ->expectsOutputToContain('Synced')
        ->assertExitCode(0);
});
