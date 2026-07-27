<?php

namespace App\Enums;

enum PlanSort: string
{
    case Name = 'name';
    case Tier = 'tier';
    case CreatedAt = 'created_at';
}
