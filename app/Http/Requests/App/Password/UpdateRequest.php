<?php

namespace App\Http\Requests\App\Password;

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
            'current_password' => UserRules::passwordCurrent(),
            'password' => UserRules::passwordNew(),
        ];
    }
}

