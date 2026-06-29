<?php

namespace App\Http\Requests\App\Tag;

use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('tag')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => TagRules::name(required: false),
        ];
    }
}

