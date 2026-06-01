<?php

uses()->group('me-email.store');

use App\Models\EmailChangeToken;
use App\Models\User;
use App\Notifications\EmailChangeNotification;
use Illuminate\Support\Facades\Notification;

test('user can request email change', function () {
    Notification::fake();

    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->postJson('/me/email', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('responses.email_change.sent')]);

    expect(EmailChangeToken::where('user_id', $user->id)->exists())->toBeTrue();

    Notification::assertSentTo($user, EmailChangeNotification::class);
});

test('request email change deletes previous tokens', function () {
    Notification::fake();

    $user = User::factory()->verified()->create();

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'old@example.com',
        'token' => 'old-hashed-token',
        'created_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($user)->postJson('/me/email', [
        'email' => 'new@example.com',
    ]);

    expect(EmailChangeToken::where('user_id', $user->id)->count())->toBe(1);
    expect(EmailChangeToken::where('user_id', $user->id)->first()->email)->toBe('new@example.com');
});

test('request email change fails when throttled', function () {
    Notification::fake();

    $user = User::factory()->verified()->create();

    EmailChangeToken::create([
        'user_id' => $user->id,
        'email' => 'recent@example.com',
        'token' => 'hashed-token',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson('/me/email', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    Notification::assertNothingSent();
});

test('request email change fails with missing email', function () {
    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->postJson('/me/email', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('request email change fails with invalid email', function () {
    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->postJson('/me/email', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('request email change fails with already taken email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->verified()->create();

    $response = $this->actingAs($user)->postJson('/me/email', [
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('request email change fails when unauthenticated', function () {
    $response = $this->postJson('/me/email', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(401);
});

test('request email change fails when unverified', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->postJson('/me/email', [
        'email' => 'new@example.com',
    ]);

    $response->assertStatus(403);
});
