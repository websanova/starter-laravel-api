<?php

uses()->group('console.plans-sync');

use App\Models\Plan;
use App\Models\Price;

test('command exits successfully when there is nothing to sync', function () {
    Price::query()->delete();

    $this->artisan('plans:sync')->assertExitCode(0);
});

test('command skips prices that have no lookup key', function () {
    Price::query()->delete();

    $plan = Plan::factory()->create();
    Price::factory()->for($plan)->create(['lookup_key' => null]);

    $this->artisan('plans:sync')
        ->expectsOutputToContain('Synced')
        ->assertExitCode(0);
});
