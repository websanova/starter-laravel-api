<?php

uses()->group('account.subscription.resume');

use App\Models\Plan;
use App\Models\User;

test('unauthenticated user cannot resume subscription', function () {
    $response = $this->patchJson('/account/subscription/resume');

    $response->assertStatus(401);
});

test('user without cancelled subscription cannot resume', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson('/account/subscription/resume');

    $response->assertStatus(403);
});

test('complimentary user cannot resume', function () {
    $plan = Plan::factory()->complimentary()->create();
    $user = User::factory()->create(['plan_id' => $plan->id]);

    $response = $this->actingAs($user)->patchJson('/account/subscription/resume');

    $response->assertStatus(403);
});

