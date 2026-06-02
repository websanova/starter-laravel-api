<?php

namespace App\Enums;

enum AccountPruneStrategy: string
{
    case Delete = 'delete';
    case Anonymize = 'anonymize';
}
