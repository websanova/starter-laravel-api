<?php

namespace App\Http\Requests\App\ResetPassword;

use App\Rules\SharedRules;
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
            'token' => SharedRules::token(),
            'email' => UserRules::email(),
            'password' => UserRules::passwordNew(),
        ];
    }
}

