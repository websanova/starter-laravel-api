<?php

namespace App\Http\Requests\Register;

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
            'name' => UserRules::name(),
            'email' => UserRules::email(),
            'password' => UserRules::password(),
        ];
    }
}
