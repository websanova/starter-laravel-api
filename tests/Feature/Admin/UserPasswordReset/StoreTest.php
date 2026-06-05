<?php

uses()->group('admin.user-password-reset.store');

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\TempPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('admin can force password reset on a regular user', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $originalPassword = $target->password;

    $response = $this->actingAs($admin)->postJson("/admin/users/{$target->id}/password-reset");

    $response->assertStatus(200)
        ->assertJsonPath('message', __('responses.admin.user.password_reset'));

    $target->refresh();
    expect($target->password)->not->toBe($originalPassword);
    expect($target->is_password_reset_required)->toBeTrue();

    Notification::assertSentTo($target, TempPasswordNotification::class);
});

test('tokens are revoked on forced password reset', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $target = User::factory()->create();
    $target->createToken('auth');
    $target->createToken('auth');

    $this->actingAs($admin)->postJson("/admin/users/{$target->id}/password-reset");

    expect($target->tokens()->count())->toBe(0);
});

test('super can force password reset on an admin', function () {
    Notification::fake();

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $response = $this->actingAs($super)->postJson("/admin/users/{$admin->id}/password-reset");

    $response->assertStatus(200);
    expect($admin->fresh()->is_password_reset_required)->toBeTrue();

    Notification::assertSentTo($admin, TempPasswordNotification::class);
});

test('admin cannot force password reset on a super user', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::Admin);

    $super = User::factory()->create();
    $super->assignRole(UserRole::Super);

    $response = $this->actingAs($admin)->postJson("/admin/users/{$super->id}/password-reset");

    $response->assertStatus(403);

    Notification::assertNothingSent();
});

test('regular user cannot force password reset', function () {
    Notification::fake();

    $user = User::factory()->create();
    $target = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/admin/users/{$target->id}/password-reset");

    $response->assertStatus(403);

    Notification::assertNothingSent();
});

test('unauthenticated user cannot force password reset', function () {
    $target = User::factory()->create();

    $response = $this->postJson("/admin/users/{$target->id}/password-reset");

    $response->assertStatus(401);
});
