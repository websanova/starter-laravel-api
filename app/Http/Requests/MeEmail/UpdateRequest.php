<?php

namespace App\Http\Requests\MeEmail;

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
            'email' => UserRules::email(ignore: $this->user()->id),
        ];
    }
}
