<?php

namespace App\Enums;

enum UserSort: string
{
    case Name = 'name';
    case CreatedAt = 'created_at';
    case LastActiveAt = 'last_active_at';
    case Email = 'email';
}
