<?php

namespace Database\Seeders;

use App\Enums\PlanInterval;
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
        Plan::firstOrCreate(
            ['slug' => PlanTier::Free->value],
            [
                'name' => 'Free',
                'features' => [
                    'bookmarks' => 10,
                    'categories' => 3,
                    'tags' => 10,
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 0,
            ],
        );

        $pro = Plan::firstOrCreate(
            ['slug' => PlanTier::Pro->value],
            [
                'name' => 'Pro',
                'features' => [
                    'bookmarks' => null,
                    'categories' => null,
                    'tags' => null,
                ],
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 1,
            ],
        );

        $pro->prices()->firstOrCreate(
            ['interval' => PlanInterval::Monthly->value],
            ['stripe_product_id' => config('subscription.stripe_products.pro.monthly')],
        );

        $pro->prices()->firstOrCreate(
            ['interval' => PlanInterval::Yearly->value],
            ['stripe_product_id' => config('subscription.stripe_products.pro.yearly')],
        );
    }
}
