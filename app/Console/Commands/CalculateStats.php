<?php

namespace App\Console\Commands;

use App\Services\CalculateStatsService;
use Illuminate\Console\Command;

class CalculateStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:calculate {--group= : Only calculate stats for a specific group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store application stats';

    /**
     * Execute the console command.
     */
    public function handle(CalculateStatsService $service): int
    {
        $count = $service->handle($this->option('group'));

        $this->info("Calculated {$count} stat(s).");

        return self::SUCCESS;
    }
}
