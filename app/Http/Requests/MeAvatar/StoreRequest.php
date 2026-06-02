<?php

namespace App\Http\Requests\MeAvatar;

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
            'avatar' => UserRules::avatar(),
        ];
    }
}
