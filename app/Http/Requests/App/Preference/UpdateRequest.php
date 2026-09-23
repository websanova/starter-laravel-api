<?php

namespace App\Http\Requests\App\Preference;

use App\Rules\BookmarkRules;
use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bookmarks_sort_by' => BookmarkRules::sortBy(),
            'bookmarks_sort_dir' => SharedRules::sortDir(),
            'bookmarks_view' => BookmarkRules::view(),
        ];
    }
}
