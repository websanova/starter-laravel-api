<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\Price;
use App\Support\ServiceResult;

interface SyncPlanPricesProvider
{
    /**
     * Sync a single plan's prices from the provider.
     */
    public function handle(Plan $plan): ServiceResult;

    /**
     * Sync every plan's prices from the provider.
     */
    public function handleAll(): ServiceResult;

    /**
     * Sync a single price from the provider.
     */
    public function handlePrice(Price $price): ServiceResult;
}
