<?php

namespace App\Console\Commands;

use App\Enums\AccountPruneStrategy;
use App\Models\User;
use Illuminate\Console\Command;

class PruneUsersDeleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:users-deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune soft-deleted users past the grace period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $gracePeriod = config('auth.delete.grace_period');
        $strategy = config('auth.delete.prune_strategy');
        $cutoff = now()->subDays($gracePeriod);

        $count = 0;

        User::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->chunkById(100, function ($users) use ($strategy, &$count) {
                foreach ($users as $user) {
                    match ($strategy) {
                        AccountPruneStrategy::Delete => $user->purge(),
                        AccountPruneStrategy::Anonymize => $user->anonymize(),
                    };

                    $count++;
                }
            });

        if ($count === 0) {
            $this->info('No users to prune.');
        } else {
            $this->info("Pruned {$count} user(s) using '{$strategy->value}' strategy.");
        }

        return self::SUCCESS;
    }
}
