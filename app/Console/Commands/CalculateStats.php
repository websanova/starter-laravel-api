<?php

namespace App\Console\Commands;

use App\Enums\StatGroup;
use App\Models\Stat;
use App\Services\Stats\ContentStatsCalculator;
use App\Services\Stats\StatCalculator;
use App\Services\Stats\SubscriptionStatsCalculator;
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
     * The registered calculators.
     *
     * @var list<class-string<StatCalculator>>
     */
    protected array $calculators = [
        SubscriptionStatsCalculator::class,
        ContentStatsCalculator::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $group = $this->option('group')
            ? StatGroup::tryFrom($this->option('group'))
            : null;

        if ($this->option('group') && is_null($group)) {
            $this->error("Invalid group: {$this->option('group')}");

            return self::FAILURE;
        }

        $count = 0;

        foreach ($this->calculators as $calculatorClass) {
            $calculator = new $calculatorClass;

            if ($group && $calculator->group() !== $group) {
                continue;
            }

            $stats = $calculator->calculate();
            $now = now();

            foreach ($stats as $key => $value) {
                Stat::updateOrCreate(
                    ['group' => $calculator->group()->value, 'key' => $key],
                    ['value' => $value, 'calculated_at' => $now],
                );

                $count++;
            }
        }

        $this->info("Calculated {$count} stat(s).");

        return self::SUCCESS;
    }
}
