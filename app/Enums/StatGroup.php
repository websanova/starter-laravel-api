<?php

namespace App\Enums;

enum StatGroup: string
{
    case Subscriptions = 'subscriptions';
    case Users = 'users';
    case Bookmarks = 'bookmarks';
    case Tags = 'tags';
}
