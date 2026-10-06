<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Terminal;

class ResetApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear caches, rebuild the database, sync plans and calculate stats (local only)';

    /**
     * Execute the console command. Each step runs in its own process so it
     * boots from the config on disk rather than what this process loaded.
     */
    public function handle(): int
    {
        if (! app()->isLocal()) {
            $this->error('app:reset can only run in the local environment.');

            return self::FAILURE;
        }

        $steps = [
            ['optimize:clear'],
            ['migrate:fresh', '--seed'],
            ['plans:sync'],
            ['stats:calculate'],
        ];

        foreach ($steps as $step) {
            $result = Process::path(base_path())
                ->forever()
                ->env(['COLUMNS' => (new Terminal)->getWidth()])
                ->run([PHP_BINARY, 'artisan', ...$step, '--ansi'], fn (string $type, string $output) => $this->output->write($output));

            if ($result->failed()) {
                $this->error('Step failed: '.implode(' ', $step));

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
