<?php

namespace App\Http\Requests\MeBookmark;

use App\Rules\BookmarkRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'url' => BookmarkRules::url(required: false),
            'title' => BookmarkRules::title(required: false),
            'description' => BookmarkRules::description(),
        ];
    }
}
