<?php

namespace App\Console\Commands;

use App\Contracts\SyncPlanPricesProvider;
use App\Models\Plan;
use Illuminate\Console\Command;

class SyncPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync plan prices from their Stripe product default prices';

    /**
     * Execute the console command.
     */
    public function handle(SyncPlanPricesProvider $service): int
    {
        $synced = 0;
        $failed = 0;

        foreach (Plan::all() as $plan) {
            $result = $service->handle($plan);

            if ($result->success) {
                $synced++;
            } else {
                $failed++;
                $this->error("Plan '{$plan->slug}': {$result->error}");
            }
        }

        $this->info("Synced {$synced} plan(s), {$failed} failed.");

        return self::SUCCESS;
    }
}
