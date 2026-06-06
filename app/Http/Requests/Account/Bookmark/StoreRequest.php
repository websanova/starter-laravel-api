<?php

namespace App\Http\Requests\Account\Bookmark;

use App\Rules\BookmarkRules;
use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => BookmarkRules::categoryId($this->user()->id),
            'url' => BookmarkRules::url(),
            'title' => BookmarkRules::title(),
            'description' => BookmarkRules::description(),
            'is_favorited' => BookmarkRules::isFavorited(),
            'tags' => ['sometimes', 'array'],
            'tags.*' => TagRules::name(),
        ];
    }
}
