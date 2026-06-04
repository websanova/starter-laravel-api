<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class SearchableObserver
{
    /**
     * Update the keywords column when searchable fields change.
     */
    public function saving(Model $model): void
    {
        if ($model->searchableFieldsAreDirty()) {
            $model->keywords = $model->toKeywords();
        }
    }
}
