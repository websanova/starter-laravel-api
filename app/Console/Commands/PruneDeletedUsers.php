<?php

namespace App\Console\Commands;

use App\Enums\AccountPruneStrategy;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $strategy = AccountPruneStrategy::from(config('auth.delete.prune_strategy'));
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
                AccountPruneStrategy::Delete => $user->forceDelete(),
                AccountPruneStrategy::Anonymize => $this->anonymize($user),
            };
        }

        $this->info("Pruned {$users->count()} user(s) using '{$strategy->value}' strategy.");

        return self::SUCCESS;
    }

    /**
     * Anonymize a user's personally identifiable information.
     */
    protected function anonymize(User $user): void
    {
        if ($user->avatar) {
            Storage::disk('s3')->delete($user->avatar);
        }

        $hash = Str::random(32);

        $user->forceFill([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => "deleted_{$hash}@anonymized.local",
            'password' => Str::random(64),
            'avatar' => null,
            'remember_token' => null,
        ])->save();

        $user->tokens()->delete();
    }
}
