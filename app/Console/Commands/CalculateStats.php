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
        $this->components->info('Calculating stats.');

        foreach ($service->groups() as $group) {
            if ($this->option('group') && $group !== $this->option('group')) {
                continue;
            }

            $this->components->task($group, function () use ($service, $group) {
                $service->handle($group);
            });
        }

        $this->newLine();

        return self::SUCCESS;
    }
}
