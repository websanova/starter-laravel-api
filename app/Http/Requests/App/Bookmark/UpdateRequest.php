<?php

namespace App\Http\Requests\App\Bookmark;

use App\Rules\BookmarkRules;
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
            'url' => BookmarkRules::url($this->user()->id, $this->route('bookmark')->id, required: false),
            'title' => BookmarkRules::title(required: false),
            'description' => BookmarkRules::description(),
            'is_favorited' => BookmarkRules::isFavorited(),
            'tag_ids' => BookmarkRules::tagIds(),
            'tag_ids.*' => BookmarkRules::tagId($this->user()->id),
        ];
    }
}

