<?php

namespace App\Services\Stats;

use App\Enums\StatGroup;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Carbon;

class SubscriptionStatsCalculator implements StatCalculator
{
    /**
     * The group this calculator belongs to.
     */
    public function group(): StatGroup
    {
        return StatGroup::Subscriptions;
    }

    /**
     * Calculate subscription stats.
     *
     * @return array<string, array>
     */
    public function calculate(): array
    {
        return [
            'signups_today' => $this->signupsForDate(today()),
            'signups_yesterday' => $this->signupsForDate(today()->subDay()),
            'signups_day_before' => $this->signupsForDate(today()->subDays(2)),
            'distribution' => $this->distribution(),
        ];
    }

    /**
     * Get signup counts by plan for a given date.
     *
     * Returns an array of objects with plan_name, plan_slug, and count
     * so the frontend can iterate and display without lookups.
     *
     * @return list<array{plan_name: string, plan_slug: string, count: int}>
     */
    protected function signupsForDate(Carbon $date): array
    {
        $plans = Plan::cached();

        $counts = User::query()
            ->whereDate('created_at', $date)
            ->whereNotNull('plan_id')
            ->selectRaw('plan_id, count(*) as count')
            ->groupBy('plan_id')
            ->pluck('count', 'plan_id');

        return $plans->map(fn (Plan $plan) => [
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'count' => (int) ($counts[$plan->id] ?? 0),
        ])->values()->all();
    }

    /**
     * Get current distribution of subscribers by plan and interval.
     *
     * Returns an array of objects with plan_name, plan_slug, interval, and count
     * so the frontend can iterate and render charts directly.
     *
     * @return list<array{plan_name: string, plan_slug: string, interval: string, count: int}>
     */
    protected function distribution(): array
    {
        $plans = Plan::cached();
        $results = [];

        foreach ($plans as $plan) {
            $monthlyCount = 0;
            $yearlyCount = 0;

            if ($plan->stripe_monthly_price_id) {
                $monthlyCount = User::query()
                    ->whereHas('subscriptions', function ($query) use ($plan) {
                        $query->where('stripe_price', $plan->stripe_monthly_price_id)
                            ->where('stripe_status', 'active');
                    })
                    ->count();
            }

            if ($plan->stripe_yearly_price_id) {
                $yearlyCount = User::query()
                    ->whereHas('subscriptions', function ($query) use ($plan) {
                        $query->where('stripe_price', $plan->stripe_yearly_price_id)
                            ->where('stripe_status', 'active');
                    })
                    ->count();
            }

            $complimentaryCount = User::query()
                ->where('plan_id', $plan->id)
                ->whereDoesntHave('subscriptions', function ($query) {
                    $query->where('stripe_status', 'active');
                })
                ->count();

            if ($plan->stripe_monthly_price_id) {
                $results[] = [
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'interval' => 'monthly',
                    'count' => $monthlyCount,
                ];
            }

            if ($plan->stripe_yearly_price_id) {
                $results[] = [
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'interval' => 'yearly',
                    'count' => $yearlyCount,
                ];
            }

            if ($plan->is_complimentary || $plan->has_no_price) {
                $results[] = [
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'interval' => 'complimentary',
                    'count' => $complimentaryCount,
                ];
            }
        }

        return $results;
    }
}
