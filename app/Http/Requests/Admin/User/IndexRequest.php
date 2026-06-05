<?php

namespace App\Http\Requests\Admin\User;

use App\Enums\Role;
use App\Enums\TrashedFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    /**
     * Cast validated enum fields after validation passes.
     */
    protected function passedValidation(): void
    {
        if ($this->has('role')) {
            $this->merge(['role' => Role::from($this->input('role'))]);
        }

        if ($this->has('trashed')) {
            $this->merge(['trashed' => TrashedFilter::from($this->input('trashed'))]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', Rule::enum(Role::class)],
            'trashed' => ['sometimes', 'string', Rule::enum(TrashedFilter::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
