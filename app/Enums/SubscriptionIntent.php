<?php

namespace App\Enums;

enum SubscriptionIntent: string
{
    case None = 'none';
    case Payment = 'payment';
    case Setup = 'setup';
}
