<?php

namespace Database\Seeders;

use App\Enums\PlanTier;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed the default plans from the PlanTier enum.
     */
    public function run(): void
    {
        $defaults = [
            PlanTier::Free->value => [
                'name' => 'Free',
                'slug' => PlanTier::Free->value,
                'monthly_price' => 0,
                'yearly_price' => 0,
                'features' => [
                    'bookmarks' => 10,
                    'categories' => 3,
                    'tags' => 10,
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 0,
            ],
            PlanTier::Pro->value => [
                'name' => 'Pro',
                'slug' => PlanTier::Pro->value,
                'stripe_monthly_price_id' => env('STRIPE_PRICE_PRO_MONTHLY'),
                'stripe_yearly_price_id' => env('STRIPE_PRICE_PRO_YEARLY'),
                'monthly_price' => 999,
                'yearly_price' => 9990,
                'features' => [
                    'bookmarks' => null,
                    'categories' => null,
                    'tags' => null,
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($defaults as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }
    }
}
