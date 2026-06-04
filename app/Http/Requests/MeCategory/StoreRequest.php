<?php

namespace App\Http\Requests\MeCategory;

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
