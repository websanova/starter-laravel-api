<?php

namespace App\Models\Concerns;

use App\Enums\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;

trait HasTrashedScope
{
    /**
     * Filter by soft delete state.
     */
    public function scopeForTrashed(Builder $query, ?TrashedFilter $trashed): void
    {
        match ($trashed) {
            TrashedFilter::Only => $query->onlyTrashed(),
            TrashedFilter::With => $query->withTrashed(),
            null => null,
        };
    }
}
