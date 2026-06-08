<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    /**
     * Filter by read status.
     */
    public function scopeForRead(Builder $query, ?bool $read): void
    {
        if (is_null($read)) {
            return;
        }

        $query->where('read_at', $read ? '!=' : '=', null);
    }
}
