<?php

namespace App\Http\Requests\Admin\Preference;

use App\Rules\SharedRules;
use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'users_sort_by' => UserRules::sortBy(),
            'users_sort_dir' => SharedRules::sortDir(),
        ];
    }
}
