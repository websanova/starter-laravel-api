<?php

namespace App\Http\Requests\Admin\User;

use App\Rules\UserRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => UserRules::firstName(required: false),
            'last_name' => UserRules::lastName(required: false),
            'locale' => UserRules::locale(),
            'timezone' => UserRules::timezone(),
        ];
    }
}
