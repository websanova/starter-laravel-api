<?php

uses()->group('app.subscription-update.store');

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Models\User;
use Database\Factories\SubscriptionFactory;
use Laravel\Cashier\Cashier;

afterEach(fn () => stripeSandboxFlush());

/**
 * A plan whose prices are the real ones in the sandbox, since the swap is made
 * against the price id held locally.
 */
function subscriptionUpdatePlan(): Plan
{
    $plan = Plan::factory()->create();

    foreach (['pro_monthly' => PlanInterval::Monthly, 'pro_yearly' => PlanInterval::Yearly] as $lookupKey => $interval) {
        $price = Cashier::stripe()->prices->all(['lookup_keys' => [$lookupKey], 'limit' => 1])->data[0];

        Price::factory()->for($plan)->create([
            'interval' => $interval,
            'stripe_price_id' => $price->id,
        ]);
    }

    return $plan;
}

/**
 * A user on a live monthly subscription at Stripe, with the local row to match.
 */
function subscriptionUpdateUser(Plan $plan): User
{
    $user = stripeSandboxUser();

    $card = stripeSandboxCard($user);

    $user->updateDefaultPaymentMethod($card->id);

    $stripeSubscription = Cashier::stripe()->subscriptions->create([
        'customer' => $user->stripe_id,
        'items' => [['price' => $plan->priceId(PlanInterval::Monthly)]],
        'default_payment_method' => $card->id,
    ]);

    $subscription = $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => $stripeSubscription->id,
        'stripe_status' => $stripeSubscription->status,
        'stripe_price' => $plan->priceId(PlanInterval::Monthly),
        'quantity' => 1,
    ]);

    $subscription->items()->create([
        'stripe_id' => $stripeSubscription->items->data[0]->id,
        'stripe_product' => $stripeSubscription->items->data[0]->price->product,
        'stripe_price' => $plan->priceId(PlanInterval::Monthly),
        'quantity' => 1,
    ]);

    return $user;
}

test('unauthenticated user cannot update a subscription', function () {
    $response = $this->postJson('/subscription/update');

    $response->assertStatus(401);
});

test('user must send a known plan and interval', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => 'nope',
        'interval' => 'weekly',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['plan', 'interval']);
});

test('user without a subscription cannot update', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'nothing_to_update');
});

test('user with a cancelled subscription cannot update', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    SubscriptionFactory::new()->create([
        'user_id' => $user->id,
        'ends_at' => now()->addDays(10),
    ]);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'pending_cancellation');
});

test('user with an outstanding payment cannot update', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    SubscriptionFactory::new()->pastDue()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'past_due');
});

test('user with an unpaid subscription cannot update', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    SubscriptionFactory::new()->unpaid()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'unpaid');
});

test('user with a subscription that never started cannot update', function () {
    $plan = Plan::factory()->paid()->create();

    $user = User::factory()->create();

    SubscriptionFactory::new()->incomplete()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'not_active');
});

test('a plan with no price for the interval cannot be moved to', function () {
    $plan = Plan::factory()->create();

    Price::factory()->for($plan)->create(['interval' => PlanInterval::Monthly]);

    $user = User::factory()->create();

    SubscriptionFactory::new()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'yearly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'plan_unavailable');
});

test('the plan and interval already held changes nothing', function () {
    $plan = subscriptionUpdatePlan();

    $user = subscriptionUpdateUser($plan);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'monthly',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'paid');

    expect($user->subscriptions()->first()->stripe_price)->toBe($plan->priceId(PlanInterval::Monthly));
})->group('stripe');

test('user with no card on file cannot update', function () {
    $plan = subscriptionUpdatePlan();

    $user = subscriptionUpdateUser($plan);

    Cashier::stripe()->subscriptions->update($user->subscription()->stripe_id, ['default_payment_method' => '']);

    $user->updateStripeCustomer(['invoice_settings' => ['default_payment_method' => '']]);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'yearly',
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('error', 'payment_method_missing');
})->group('stripe');

test('user moving from monthly to yearly carries the new price', function () {
    $plan = subscriptionUpdatePlan();

    $user = subscriptionUpdateUser($plan);

    $response = $this->actingAs($user)->postJson('/subscription/update', [
        'plan' => $plan->slug,
        'interval' => 'yearly',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'paid');

    expect($user->subscriptions()->first()->stripe_price)->toBe($plan->priceId(PlanInterval::Yearly))
        ->and(Cashier::stripe()->subscriptions->retrieve($user->subscription()->stripe_id)->items->data[0]->price->id)
        ->toBe($plan->priceId(PlanInterval::Yearly));
})->group('stripe');
