<?php

namespace App\Http\Requests\Auth\Login;

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
            'email' => UserRules::email(),
            'password' => UserRules::password(),
        ];
    }
}
