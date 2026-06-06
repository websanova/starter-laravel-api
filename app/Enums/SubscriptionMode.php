<?php

namespace App\Enums;

enum SubscriptionMode: string
{
    case Freemium = 'freemium';
    case Trial = 'trial';
    case Required = 'required';
}
