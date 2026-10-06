<?php

namespace App\Console\Commands;

use App\Contracts\SyncPlanPricesProvider;
use App\Models\Plan;
use Illuminate\Console\Command;
use Illuminate\Console\View\TaskResult;

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
    public function handle(SyncPlanPricesProvider $syncPlanPrices): int
    {
        $this->components->info('Syncing plans.');

        foreach (Plan::all() as $plan) {
            $result = null;

            $this->components->task($plan->slug, function () use ($syncPlanPrices, $plan, &$result) {
                $result = $syncPlanPrices->handle($plan);

                return $result->success ? TaskResult::Success->value : TaskResult::Failure->value;
            });

            if (! $result->success) {
                $this->components->error("Plan '{$plan->slug}': {$result->error}");
            }
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
