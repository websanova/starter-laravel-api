<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    /**
     * Get the title from the notification data.
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data['title'] ?? null,
        );
    }

    /**
     * Get the body from the notification data.
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->data['body'] ?? null,
        );
    }

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
