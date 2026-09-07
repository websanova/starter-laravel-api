<?php

namespace App\Enums;

enum PlanInterval: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    /**
     * The public display name, translated per request or per recipient locale.
     */
    public function displayName(): string
    {
        return __('plans.interval.' . $this->value);
    }
}
