<?php

namespace App\Http\Requests\Account\Bookmark;

use App\Enums\BookmarkSort;
use App\Enums\SortDirection;
use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => SharedRules::id(allowZero: true),
            'sort_by' => ['sometimes', 'string', Rule::enum(BookmarkSort::class)],
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

        if (isset($data['sort_by'])) {
            $data['sort_by'] = BookmarkSort::from($data['sort_by']);
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
