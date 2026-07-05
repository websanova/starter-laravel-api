<?php

namespace App\Http\Requests\App\Register;

use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => UserRules::firstName(false),
            'last_name' => UserRules::lastName(false),
            'email' => UserRules::emailNew(),
            'password' => UserRules::passwordNew(),
        ];
    }
}

