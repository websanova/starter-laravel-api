<?php

namespace App\Contracts;

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Price;
use App\Support\ServiceResult;

interface PlanSyncProvider
{
    /**
     * Sync every plan's prices from the provider.
     */
    public function syncAll(?PlanInterval $interval = null): ServiceResult;

    /**
     * Sync a single plan's prices, optionally limited to one interval.
     */
    public function sync(Plan $plan, ?PlanInterval $interval = null): ServiceResult;

    /**
     * Sync a single price from the provider.
     */
    public function syncPrice(Price $price): ServiceResult;
}
