<?php

namespace App\Contracts;

use App\Support\ServiceResult;

interface ResolvePromotionCodeProvider
{
    /**
     * Resolve a promotion code string against the provider, returning the
     * provider's own promotion code object on success.
     */
    public function handle(string $code): ServiceResult;
}
