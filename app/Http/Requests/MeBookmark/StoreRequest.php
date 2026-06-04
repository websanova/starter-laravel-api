<?php

namespace App\Http\Requests\MeBookmark;

use App\Rules\BookmarkRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'url' => BookmarkRules::url(),
            'title' => BookmarkRules::title(),
            'description' => BookmarkRules::description(),
        ];
    }
}
