<?php

namespace App\Enums;

enum PlanSort: string
{
    case Name = 'name';
    case SortOrder = 'sort_order';
    case CreatedAt = 'created_at';
}
