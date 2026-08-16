<?php

uses()->group('listener.commit-plan');

use App\Models\Plan;
use App\Models\User;
use App\Notifications\PlanCancelledNotification;
use App\Notifications\PlanChangedNotification;
use App\Notifications\PlanResumedNotification;
use App\Notifications\PlanSubscribedNotification;
use Database\Factories\SubscriptionFactory;
use Illuminate\Support\Facades\Notification;
use Laravel\Cashier\Events\WebhookHandled;

function subscriptionPayload(string $type, string $customer, string $status, string $priceId, array $previous = [], bool $cancelAtPeriodEnd = false): array
{
    return [
        'type' => $type,
        'data' => [
            'object' => [
                'customer' => $customer,
                'status' => $status,
                'cancel_at_period_end' => $cancelAtPeriodEnd,
                'default_payment_method' => null,
                'items' => ['data' => [['price' => ['id' => $priceId]]]],
            ],
            'previous_attributes' => $previous,
        ],
    ];
}

test('created at active writes the plan and announces it', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_active']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.created',
        'cus_active',
        'active',
        $priceId,
    )));

    expect($user->fresh()->plan_id)->toBe($plan->id);

    Notification::assertSentTo($user, PlanSubscribedNotification::class);
});

test('created at incomplete announces nothing and grants no plan', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_incomplete']);
    SubscriptionFactory::new()->incomplete()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.created',
        'cus_incomplete',
        'incomplete',
        $priceId,
    )));

    expect($user->fresh()->plan_id)->toBeNull();

    Notification::assertNothingSent();
});

test('update flipping incomplete to active announces the subscription', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_flip']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.updated',
        'cus_flip',
        'active',
        $priceId,
        ['status' => 'incomplete'],
    )));

    expect($user->fresh()->plan_id)->toBe($plan->id);

    Notification::assertSentTo($user, PlanSubscribedNotification::class);
});

test('update carrying a price change announces a plan change', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_swap']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.updated',
        'cus_swap',
        'active',
        $priceId,
        ['items' => []],
    )));

    Notification::assertSentTo($user, PlanChangedNotification::class);
});

test('update setting cancel at period end announces a cancellation', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_cancel']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.updated',
        'cus_cancel',
        'active',
        $priceId,
        ['cancel_at_period_end' => false],
        cancelAtPeriodEnd: true,
    )));

    Notification::assertSentTo($user, PlanCancelledNotification::class);
});

test('update clearing cancel at period end announces a resume', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_resume']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.updated',
        'cus_resume',
        'active',
        $priceId,
        ['cancel_at_period_end' => true],
    )));

    Notification::assertSentTo($user, PlanResumedNotification::class);
});

test('the plan is resolved from the local subscription not the payload price', function () {
    Notification::fake();

    $held = Plan::factory()->paid()->create();
    $other = Plan::factory()->paid()->create();

    $heldPriceId = $held->prices->first()->stripe_price_id;
    $otherPriceId = $other->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_divergent']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $heldPriceId]);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.updated',
        'cus_divergent',
        'active',
        $otherPriceId,
        ['status' => 'incomplete'],
    )));

    expect($user->fresh()->plan_id)->toBe($held->id);
});

test('an unknown customer is ignored', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_known']);

    event(new WebhookHandled(subscriptionPayload(
        'customer.subscription.created',
        'cus_stranger',
        'active',
        $priceId,
    )));

    expect($user->fresh()->plan_id)->toBeNull();

    Notification::assertNothingSent();
});

test('unrelated webhook types are ignored', function () {
    Notification::fake();

    $plan = Plan::factory()->paid()->create();
    $priceId = $plan->prices->first()->stripe_price_id;

    $user = User::factory()->create(['stripe_id' => 'cus_unrelated']);
    SubscriptionFactory::new()->create(['user_id' => $user->id, 'stripe_price' => $priceId]);

    event(new WebhookHandled(subscriptionPayload(
        'invoice.payment_succeeded',
        'cus_unrelated',
        'active',
        $priceId,
    )));

    expect($user->fresh()->plan_id)->toBeNull();

    Notification::assertNothingSent();
});
