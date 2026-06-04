<?php

namespace App\Http\Requests\Account\Category;

use App\Rules\CategoryRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => CategoryRules::name(),
        ];
    }
}
