<?php

namespace App\Enums;

enum SubscriptionIntent: string
{
    case Payment = 'payment';
    case Setup = 'setup';
}
