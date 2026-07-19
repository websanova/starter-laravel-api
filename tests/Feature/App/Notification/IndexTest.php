<?php

uses()->group('app.notification.index');

use App\Models\Notification;
use App\Models\User;

test('user can list their notifications', function () {
    $user = User::factory()->create();

    $user->notifications()->createMany([
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Welcome!', 'body' => 'Your account has been created.']],
        ['id' => \Str::uuid(), 'type' => 'App\Notifications\PlanChangedNotification', 'data' => ['title' => 'Plan Updated', 'body' => 'Your plan has been changed to Pro.']],
    ]);

    $response = $this->actingAs($user)->getJson('/notifications');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'type', 'data', 'read_at', 'created_at']],
            'meta' => ['current_page', 'per_page', 'total'],
        ]);
});

test('user only sees their own notifications', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Welcome!']]);
    $other->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Welcome!']]);

    $response = $this->actingAs($user)->getJson('/notifications');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('user can filter read notifications', function () {
    $user = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Read'], 'read_at' => now()]);
    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Unread'], 'read_at' => null]);

    $response = $this->actingAs($user)->getJson('/notifications?read=1');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.data.title', 'Read');
});

test('user can filter unread notifications', function () {
    $user = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Read'], 'read_at' => now()]);
    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Unread'], 'read_at' => null]);

    $response = $this->actingAs($user)->getJson('/notifications?read=0');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.data.title', 'Unread');
});

test('no read filter returns all notifications', function () {
    $user = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Read'], 'read_at' => now()]);
    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Unread'], 'read_at' => null]);

    $response = $this->actingAs($user)->getJson('/notifications');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('can set per page limit', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Test']]);
    }

    $response = $this->actingAs($user)->getJson('/notifications?per_page=2');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('notifications are sorted by latest first', function () {
    $user = User::factory()->create();

    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'Old'], 'created_at' => now()->subDays(2)]);
    $user->notifications()->create(['id' => \Str::uuid(), 'type' => 'App\Notifications\WelcomeNotification', 'data' => ['title' => 'New'], 'created_at' => now()]);

    $response = $this->actingAs($user)->getJson('/notifications');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.data.title', 'New')
        ->assertJsonPath('data.1.data.title', 'Old');
});

test('invalid read filter returns validation error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/notifications?read=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors('read');
});

test('unauthenticated user cannot list notifications', function () {
    $response = $this->getJson('/notifications');

    $response->assertStatus(401);
});


