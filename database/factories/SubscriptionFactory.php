<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

/**
 * Cashier ships the Subscription model but no factory for it, and the model
 * lives outside App\Models so the default factory resolution never finds this
 * class. Tests instantiate it directly with SubscriptionFactory::new().
 *
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'default',
            'stripe_id' => 'sub_' . fake()->unique()->bothify('##??##'),
            'stripe_status' => StripeSubscription::STATUS_ACTIVE,
            'stripe_price' => 'price_' . fake()->unique()->bothify('##??##'),
            'quantity' => 1,
        ];
    }

    /**
     * Indicate that the subscription is running a trial.
     */
    public function trialing(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_status' => StripeSubscription::STATUS_TRIALING,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Indicate that the subscription is awaiting its first payment.
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_status' => StripeSubscription::STATUS_INCOMPLETE,
        ]);
    }

    /**
     * Indicate that the subscription has a failed renewal.
     */
    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_status' => StripeSubscription::STATUS_PAST_DUE,
        ]);
    }

    /**
     * Indicate that the subscription exhausted its retries.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_status' => StripeSubscription::STATUS_UNPAID,
        ]);
    }
}
