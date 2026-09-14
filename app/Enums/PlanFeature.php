<?php

namespace App\Enums;

enum PlanFeature: string
{
    case Bookmarks = 'bookmarks';
    case Tags = 'tags';

    /**
     * Get the user relationship name for countable features.
     */
    public function relation(): ?string
    {
        return match ($this) {
            self::Bookmarks => 'bookmarks',
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
