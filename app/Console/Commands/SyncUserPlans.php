<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncUserPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute the cached plan on every user from their complimentary grant or live subscription';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = 0;

        User::with('subscriptions')->chunkById(500, function ($users) use (&$updated) {
            foreach ($users as $user) {
                $user->fillPlan();

                if ($user->isDirty('plan_id')) {
                    $user->save();
                    $updated++;
                }
            }
        });

        $this->info("Updated {$updated} user(s).");

        return self::SUCCESS;
    }
}
