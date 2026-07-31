<?php

uses()->group('app.subscription-resume.store');

use App\Models\Plan;
use App\Models\User;

test('unauthenticated user cannot resume subscription', function () {
    $response = $this->postJson('/subscription/resume');

    $response->assertStatus(401);
});

test('user without cancelled subscription cannot resume', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/resume');

    $response->assertStatus(403);
});

test('complimentary user cannot resume', function () {
    $plan = Plan::factory()->create();
    $user = User::factory()->complimentary($plan)->create();

    $response = $this->actingAs($user)->postJson('/subscription/resume');

    $response->assertStatus(403);
});


