<?php

namespace App\Http\Requests\Admin\Plan;

use App\Enums\PlanSort;
use App\Enums\SortDirection;
use App\Rules\PlanRules;
use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\Plan::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'active' => PlanRules::isActive(),
            'sort_by' => PlanRules::sortBy(),
            'sort_dir' => SharedRules::sortDir(),
            'per_page' => SharedRules::perPage(),
        ];
    }

    /**
     * Get the validated data with enum fields cast to their types.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        if (array_key_exists('active', $data)) {
            $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);
        }

        if (isset($data['sort_by'])) {
            $data['sort_by'] = PlanSort::from($data['sort_by']);
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
