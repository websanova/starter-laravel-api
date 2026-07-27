<?php

uses()->group('notification.dispatch');

use App\Models\Plan;
use App\Models\User;
use App\Notifications\PlanCancelledNotification;
use App\Notifications\PlanChangedNotification;
use App\Notifications\PlanResumedNotification;
use App\Notifications\PlanSubscribedNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Notification;

test('welcome notification sends via mail and database', function () {
    $user = User::factory()->create();

    $user->notify(new WelcomeNotification);

    $this->assertDatabaseHas('notifications', [
        'notifiable_id' => $user->id,
        'notifiable_type' => User::class,
        'type' => WelcomeNotification::class,
    ]);
});

test('plan subscribed notification sends via mail and database', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    $user->notify(new PlanSubscribedNotification($plan));

    Notification::assertSentTo($user, PlanSubscribedNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels) && in_array('database', $channels);
    });
});

test('plan changed notification sends via mail and database', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $user = User::factory()->create();

    $user->notify(new PlanChangedNotification($plan));

    Notification::assertSentTo($user, PlanChangedNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels) && in_array('database', $channels);
    });
});

test('plan cancelled notification sends via mail and database', function () {
    Notification::fake();

    $user = User::factory()->create();

    $user->notify(new PlanCancelledNotification);

    Notification::assertSentTo($user, PlanCancelledNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels) && in_array('database', $channels);
    });
});

test('plan resumed notification sends via mail and database', function () {
    Notification::fake();

    $user = User::factory()->create();

    $user->notify(new PlanResumedNotification);

    Notification::assertSentTo($user, PlanResumedNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels) && in_array('database', $channels);
    });
});

test('plan subscribed notification includes plan name in data', function () {
    $plan = Plan::where('slug', 'pro')->first();
    $user = User::factory()->create(['locale' => 'en-US']);

    $user->notify(new PlanSubscribedNotification($plan));

    $notification = $user->notifications()->first();

    expect($notification->data['title'])->toBe('Thanks for Subscribing!');
    expect($notification->data['body'])->toContain('Starter Pro');
});

test('plan changed notification includes plan name in data', function () {
    $plan = Plan::where('slug', 'free')->first();
    $user = User::factory()->create(['locale' => 'en-US']);

    $user->notify(new PlanChangedNotification($plan));

    $notification = $user->notifications()->first();

    expect($notification->data['title'])->toBe('Plan Updated');
    expect($notification->data['body'])->toContain('Starter Basic');
});

test('plan cancelled notification data', function () {
    $user = User::factory()->create(['locale' => 'en-US']);

    $user->notify(new PlanCancelledNotification);

    $notification = $user->notifications()->first();

    expect($notification->data['title'])->toBe('Plan Cancelled');
});

test('plan resumed notification data', function () {
    $user = User::factory()->create(['locale' => 'en-US']);

    $user->notify(new PlanResumedNotification);

    $notification = $user->notifications()->first();

    expect($notification->data['title'])->toBe('Plan Resumed');
});

test('notification renders in the user preferred locale', function () {
    $user = User::factory()->create(['locale' => 'fr-CA']);

    $user->notify(new PlanCancelledNotification);

    $notification = $user->notifications()->first();

    expect($notification->data['title'])->toBe(__('notifications.plan_cancelled.subject', [], 'fr_CA'));
});

test('welcome notification stores in database', function () {
    $user = User::factory()->create(['locale' => 'en-US']);

    $user->notify(new WelcomeNotification);

    $notification = $user->notifications()->first();

    expect($notification->data['title'])->toBe('Welcome!');
    expect($notification->data['body'])->toBe('Your account has been created successfully.');
});
