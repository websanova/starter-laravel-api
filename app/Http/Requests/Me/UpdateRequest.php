<?php

namespace App\Http\Requests\Me;

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
            'first_name' => UserRules::firstName(required: false),
            'last_name' => UserRules::lastName(required: false),
            'email' => UserRules::email(required: false, ignore: $this->user()->id),
        ];
    }
}
