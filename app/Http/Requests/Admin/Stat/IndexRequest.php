<?php

namespace App\Http\Requests\Admin\Stat;

use App\Enums\StatGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\Stat::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'group' => ['sometimes', 'string', Rule::enum(StatGroup::class)],
        ];
    }
}
