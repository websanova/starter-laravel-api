<?php

namespace Database\Factories;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
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
            'features' => [],
            'is_active' => true,
            'is_public' => true,
            'tier' => 0,
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
        return $this->state(fn (array $attributes) => []);
    }

    /**
     * Indicate that the plan is a paid plan with monthly and yearly prices.
     */
    public function paid(): static
    {
        return $this->afterCreating(function (Plan $plan) {
            Price::factory()->for($plan)->create([
                'interval' => PlanInterval::Monthly,
                'amount' => 999,
            ]);

            Price::factory()->for($plan)->create([
                'interval' => PlanInterval::Yearly,
                'amount' => 9990,
            ]);
        });
    }
}
