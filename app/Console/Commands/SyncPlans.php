<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\PlanSyncService;
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
    public function handle(PlanSyncService $service): int
    {
        $synced = 0;
        $failed = 0;

        foreach (Plan::all() as $plan) {
            $result = $service->sync($plan);

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
