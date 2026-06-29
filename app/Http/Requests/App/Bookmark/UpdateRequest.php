<?php

namespace App\Http\Requests\App\Bookmark;

use App\Rules\BookmarkRules;
use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('bookmark')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => BookmarkRules::categoryId($this->user()->id),
            'url' => BookmarkRules::url(required: false),
            'title' => BookmarkRules::title(required: false),
            'description' => BookmarkRules::description(),
            'is_favorited' => BookmarkRules::isFavorited(),
            'tags' => ['sometimes', 'array'],
            'tags.*' => TagRules::name(),
        ];
    }
}

