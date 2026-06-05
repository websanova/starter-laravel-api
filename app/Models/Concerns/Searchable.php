<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Build the keywords string from the model's searchable fields.
     */
    public function toKeywords(): string
    {
        return collect($this->searchable)
            ->map(fn ($field) => $this->getAttribute($field))
            ->filter()
            ->implode(' ');
    }

    /**
     * Filter by fulltext search on the keywords column.
     */
    public function scopeForKeywordsSearch(Builder $query, ?string $term): void
    {
        if (is_null($term)) {
            return;
        }

        $term = trim($term);

        if ($term === '') {
            return;
        }

        if ($query->getConnection()->getDriverName() === 'mysql') {
            $query->whereRaw(
                'MATCH(keywords) AGAINST(? IN BOOLEAN MODE)',
                [$term . '*']
            );
        } else {
            $query->where('keywords', 'like', '%' . $term . '%');
        }
    }

    /**
     * Search entry point for controllers. Override in models to add extra clauses.
     */
    public function scopeForSearch(Builder $query, ?string $term): void
    {
        $this->scopeForKeywordsSearch($query, $term);
    }

    /**
     * Refresh the keywords column from the searchable fields.
     */
    public function refreshKeywords(): void
    {
        $this->updateQuietly(['keywords' => $this->toKeywords()]);
    }

    /**
     * Check if any searchable fields have changed.
     */
    public function searchableFieldsAreDirty(): bool
    {
        return $this->isDirty($this->searchable);
    }
}
