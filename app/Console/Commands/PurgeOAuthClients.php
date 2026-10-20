<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Passport;

class PurgeOAuthClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:purge-oauth-clients
                            {--hours=24 :The number of hours to retain unused clients}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge OAuth clients that were registered but never used';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'));

        // A client that completed authorization has a token or auth code. One with neither past the cutoff
        // was only ever registered, an MCP Client registers again on its next connect.
        $count = Passport::client()->newQuery()
            ->where('created_at', '<', $cutoff)
            ->whereDoesntHave('tokens')
            ->whereNotIn('id', Passport::authCode()->newQuery()->select('client_id'))
            ->delete();

        $this->components->info('Purging unused OAuth clients.');
        $this->components->twoColumnDetail("Unused for more than {$cutoff->longAbsoluteDiffForHumans()}", "{$count} client(s)");
        $this->newLine();

        return self::SUCCESS;
    }
}
