<?php

uses()->group('account.notification.read');

use App\Models\User;

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();

    $user->notifications()->createMany([
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'One']],
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Two']],
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Three']],
    ]);

    $response = $this->actingAs($user)->postJson('/account/notifications/read');

    $response->assertStatus(204);
    expect($user->unreadNotifications()->count())->toBe(0);
});

test('mark all read only affects unread notifications', function () {
    $user = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Already read'], 'read_at' => now()->subHour()]);
    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Unread']]);

    $response = $this->actingAs($user)->postJson('/account/notifications/read');

    $response->assertStatus(204);
    expect($user->notifications()->whereNull('read_at')->count())->toBe(0);
    expect($user->notifications()->count())->toBe(2);
});

test('mark all read with no notifications returns 204', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/account/notifications/read');

    $response->assertStatus(204);
});

test('mark all read does not affect other users', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Mine']]);
    $other->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Theirs']]);

    $this->actingAs($user)->postJson('/account/notifications/read');

    expect($other->unreadNotifications()->count())->toBe(1);
});

test('unauthenticated user cannot mark all as read', function () {
    $response = $this->postJson('/account/notifications/read');

    $response->assertStatus(401);
});
