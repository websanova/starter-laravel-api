<?php

uses()->group('app.sync.show');

use App\Models\User;

test('user can get sync counters', function () {
    $user = User::factory()->create();

    $user->notifications()->createMany([
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'One']],
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Two']],
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Three'], 'read_at' => now()],
    ]);

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
    $response->assertJsonPath('data.notifications_unread', 2);
});

test('sync returns zero when there are no unread notifications', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
    $response->assertJsonPath('data.notifications_unread', 0);
});

test('sync does not count other users notifications', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $other->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Theirs']]);

    $response = $this->actingAs($user)->getJson('/sync');

    $response->assertStatus(200);
    $response->assertJsonPath('data.notifications_unread', 0);
});

test('unauthenticated user cannot get sync counters', function () {
    $response = $this->getJson('/sync');

    $response->assertStatus(401);
});
