<?php

namespace App\Services\Stats;

use App\Enums\StatGroup;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Tag;

class ContentStatsCalculator implements StatCalculator
{
    /**
     * The group this calculator belongs to.
     */
    public function group(): StatGroup
    {
        return StatGroup::Content;
    }

    /**
     * Calculate content stats.
     *
     * @return array<string, array>
     */
    public function calculate(): array
    {
        return [
            'bookmarks' => $this->totals(Bookmark::class),
            'categories' => $this->totals(Category::class),
            'tags' => $this->totals(Tag::class),
        ];
    }

    /**
     * Get total count for a model.
     *
     * @return array{total: int}
     */
    protected function totals(string $model): array
    {
        return [
            'total' => $model::count(),
        ];
    }
}
