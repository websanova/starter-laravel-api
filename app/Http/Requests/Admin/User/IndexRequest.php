<?php

namespace App\Http\Requests\Admin\User;

use App\Enums\Role;
use App\Enums\TrashedFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
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

    /**
     * Get the validated data with enum fields cast to their types.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        if (isset($data['role'])) {
            $data['role'] = Role::from($data['role']);
        }

        if (isset($data['trashed'])) {
            $data['trashed'] = TrashedFilter::from($data['trashed']);
        }

        if (!is_null($key)) {
            return data_get($data, $key, $default);
        }

        return $data;
    }
}
