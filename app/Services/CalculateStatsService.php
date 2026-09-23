<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Plan;
use App\Models\Stat;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;

class CalculateStatsService
{
    /**
     * Calculate and store stats, optionally filtered by group.
     */
    public function handle(?string $group = null): int
    {
        $count = 0;

        foreach ($this->queries() as $g => $groupQueries) {
            if ($group && $g !== $group) {
                continue;
            }

            foreach ($groupQueries as $key => $entry) {
                foreach ($this->dateRanges() as $range => $dates) {
                    $q = clone $entry['query'];

                    if ($dates) {
                        $q->whereBetween($entry['column'] ?? 'created_at', $dates);
                    }

                    Stat::updateOrCreate(
                        ['group' => $g, 'key' => "{$key}_{$range}"],
                        ['value' => ['count' => $q->count()], 'calculated_at' => now()],
                    );

                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * All stat queries grouped by their stat group. The column is what the
     * date ranges filter on, defaulting to created_at when left out.
     *
     * @return array<string, array<string, array{query: \Illuminate\Database\Eloquent\Builder, column?: string}>>
     */
    protected function queries(): array
    {
        $queries = [];

        foreach (Plan::cached() as $plan) {
            $queries['subscriptions']["signups_{$plan->slug}"] = ['query' => User::query()->where('plan_id', $plan->id)];

            foreach ($plan->prices as $price) {
                if (!$price->stripe_price_id) {
                    continue;
                }

                $queries['subscriptions']["{$plan->slug}_{$price->interval->value}"] = ['query' => User::query()
                    ->whereHas('subscriptions', fn ($q) => $q
                        ->where('stripe_price', $price->stripe_price_id)
                        ->where('stripe_status', 'active'))];
            }
        }

        $queries['users']['registrations'] = ['query' => User::query()];

        $queries['users']['active'] = [
            'query' => User::query()->whereNotNull('last_active_at'),
            'column' => 'last_active_at',
        ];

        $queries['bookmarks']['total'] = ['query' => Bookmark::query()];
        $queries['tags']['total'] = ['query' => Tag::query()];

        return $queries;
    }

    /**
     * Date ranges for stat calculation.
     *
     * @return array<string, ?array{0: Carbon, 1: Carbon}>
     */
    protected function dateRanges(): array
    {
        $tz = config('stats.timezone', 'America/New_York');

        return [
            'all' => null,
            'today' => [Carbon::today($tz)->utc(), Carbon::today($tz)->endOfDay()->utc()],
            'yesterday' => [Carbon::yesterday($tz)->utc(), Carbon::yesterday($tz)->endOfDay()->utc()],
            'day_before' => [Carbon::today($tz)->subDays(2)->utc(), Carbon::today($tz)->subDays(2)->endOfDay()->utc()],
        ];
    }
}
