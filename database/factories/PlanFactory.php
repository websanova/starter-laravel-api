<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(2),
            'monthly_price' => 0,
            'yearly_price' => 0,
            'features' => [],
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plan is not public.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the plan is a complimentary plan (non-free, no prices).
     */
    public function complimentary(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_monthly_price_id' => null,
            'stripe_yearly_price_id' => null,
            'monthly_price' => 0,
            'yearly_price' => 0,
        ]);
    }

    /**
     * Indicate that the plan is a paid plan.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_monthly_price_id' => 'price_monthly_' . fake()->unique()->bothify('##??##'),
            'stripe_yearly_price_id' => 'price_yearly_' . fake()->unique()->bothify('##??##'),
            'monthly_price' => 999,
            'yearly_price' => 9990,
        ]);
    }
}
