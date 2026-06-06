<?php

namespace App\Enums;

enum PlanFeature: string
{
    case Bookmarks = 'bookmarks';
    case Categories = 'categories';
    case Tags = 'tags';

    /**
     * Get the user relationship name for countable features.
     */
    public function relation(): ?string
    {
        return match ($this) {
            self::Bookmarks => 'bookmarks',
            self::Categories => 'categories',
            self::Tags => 'tags',
        };
    }

    /**
     * Whether this feature is checked by counting a relationship.
     */
    public function isCountable(): bool
    {
        return !is_null($this->relation());
    }
}
