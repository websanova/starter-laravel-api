<?php

namespace App\Http\Requests\Admin\User;

use App\Enums\SortDirection;
use App\Enums\TrashedFilter;
use App\Enums\UserRole;
use App\Enums\UserSort;
use App\Rules\SharedRules;
use App\Rules\UserRules;
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
     * Normalize comma-separated values into arrays.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('role') && is_string($this->role)) {
            $this->merge(['role' => explode(',', $this->role)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes'],
            'role.*' => ['string', Rule::enum(UserRole::class)],
            'trashed' => ['sometimes', 'string', Rule::enum(TrashedFilter::class)],
            'sort_by' => UserRules::sortBy(),
            'sort_dir' => SharedRules::sortDir(),
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
            $data['role'] = array_map(
                fn ($role) => UserRole::from($role),
                (array) $data['role'],
            );
        }

        if (isset($data['trashed'])) {
            $data['trashed'] = TrashedFilter::from($data['trashed']);
        }

        if (isset($data['sort_by'])) {
            $data['sort_by'] = UserSort::from($data['sort_by']);
        }

        if (isset($data['sort_dir'])) {
            $data['sort_dir'] = SortDirection::from($data['sort_dir']);
        }

        if (!is_null($key)) {
            return data_get($data, $key, $default);
        }

        return $data;
    }
}
