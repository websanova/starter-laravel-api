<?php

namespace App\Services\Stats;

use App\Enums\StatGroup;

interface StatCalculator
{
    /**
     * The group this calculator belongs to.
     */
    public function group(): StatGroup;

    /**
     * Calculate stats and return key-value pairs.
     *
     * @return array<string, array>
     */
    public function calculate(): array;
}
