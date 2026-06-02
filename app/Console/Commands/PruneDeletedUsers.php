<?php

namespace App\Console\Commands;

use App\Enums\AccountPruneStrategy;
use App\Models\User;
use Illuminate\Console\Command;

class PruneDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:prune-deleted';

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

        $users = User::onlyTrashed()
            ->where('deleted_at', '<=', $cutoff)
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users to prune.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            match ($strategy) {
                AccountPruneStrategy::Delete => $user->purge(),
                AccountPruneStrategy::Anonymize => $user->anonymize(),
            };
        }

        $this->info("Pruned {$users->count()} user(s) using '{$strategy->value}' strategy.");

        return self::SUCCESS;
    }
}
