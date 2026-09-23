<?php

namespace App\Enums;

enum StatGroup: string
{
    case Subscriptions = 'subscriptions';
    case Bookmarks = 'bookmarks';
    case Tags = 'tags';
}
