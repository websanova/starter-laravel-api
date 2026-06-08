<?php

uses()->group('account.notification.update');

use App\Models\User;

test('user can mark a notification as read', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => \Str::uuid(),
        'type' => 'App\Notifications\WelcomeNotification',
        'data' => ['title' => 'Welcome!'],
    ]);

    $response = $this->actingAs($user)->patchJson("/account/notifications/{$notification->id}", [
        'read' => true,
    ]);

    $response->assertStatus(204);
    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('user can mark a notification as unread', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => \Str::uuid(),
        'type' => 'App\Notifications\WelcomeNotification',
        'data' => ['title' => 'Welcome!'],
        'read_at' => now(),
    ]);

    $response = $this->actingAs($user)->patchJson("/account/notifications/{$notification->id}", [
        'read' => false,
    ]);

    $response->assertStatus(204);
    expect($notification->fresh()->read_at)->toBeNull();
});

test('user cannot update another user notification', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $notification = $other->notifications()->create([
        'id' => \Str::uuid(),
        'type' => 'App\Notifications\WelcomeNotification',
        'data' => ['title' => 'Welcome!'],
    ]);

    $response = $this->actingAs($user)->patchJson("/account/notifications/{$notification->id}", [
        'read' => true,
    ]);

    $response->assertStatus(403);
});

test('read field is required', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => \Str::uuid(),
        'type' => 'App\Notifications\WelcomeNotification',
        'data' => ['title' => 'Welcome!'],
    ]);

    $response = $this->actingAs($user)->patchJson("/account/notifications/{$notification->id}", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('read');
});

test('read field must be boolean', function () {
    $user = User::factory()->create();

    $notification = $user->notifications()->create([
        'id' => \Str::uuid(),
        'type' => 'App\Notifications\WelcomeNotification',
        'data' => ['title' => 'Welcome!'],
    ]);

    $response = $this->actingAs($user)->patchJson("/account/notifications/{$notification->id}", [
        'read' => 'invalid',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('read');
});

test('unauthenticated user cannot update notification', function () {
    $response = $this->patchJson('/account/notifications/some-uuid', [
        'read' => true,
    ]);

    $response->assertStatus(401);
});
