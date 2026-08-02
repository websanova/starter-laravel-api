<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\Price;
use App\Support\ServiceResult;

interface PlanSyncProvider
{
    /**
     * Sync every plan's prices from the provider.
     */
    public function syncAll(): ServiceResult;

    /**
     * Sync a single plan's prices from the provider.
     */
    public function sync(Plan $plan): ServiceResult;

    /**
     * Sync a single price from the provider.
     */
    public function syncPrice(Price $price): ServiceResult;
}
