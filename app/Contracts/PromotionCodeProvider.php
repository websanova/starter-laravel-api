<?php

namespace App\Contracts;

use App\Support\ServiceResult;

interface PromotionCodeProvider
{
    /**
     * Resolve a promotion code string against the provider, returning the
     * provider's own promotion code object on success.
     */
    public function resolve(string $code): ServiceResult;
}
