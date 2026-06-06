<?php

namespace App\Http\Requests\Admin\UserTag;

use App\Enums\SortDirection;
use App\Enums\TagSort;
use App\Rules\SharedRules;
use App\Rules\TagRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sort_by' => TagRules::sortBy(),
            'sort_dir' => SharedRules::sortDir(),
        ];
    }

    /**
     * Get the validated data with enum fields cast to their types.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        if (isset($data['sort_by'])) {
            $data['sort_by'] = TagSort::from($data['sort_by']);
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
