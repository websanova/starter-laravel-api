<?php

namespace Database\Factories;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'interval' => fake()->randomElement(PlanInterval::cases()),
            'stripe_product_id' => 'prod_' . fake()->unique()->bothify('##??##'),
            'stripe_price_id' => 'price_' . fake()->unique()->bothify('##??##'),
            'amount' => fake()->numberBetween(500, 50000),
            'currency' => config('cashier.currency'),
        ];
    }
}
