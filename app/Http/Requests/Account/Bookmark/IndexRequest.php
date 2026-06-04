<?php

namespace App\Http\Requests\Account\Bookmark;

use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => SharedRules::id(allowZero: true),
            'per_page' => SharedRules::perPage(),
        ];
    }
}
