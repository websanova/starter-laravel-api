<?php

namespace App\Http\Requests\Account\Notification;

use App\Rules\NotificationRules;
use App\Rules\SharedRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'read' => NotificationRules::read(),
            'per_page' => SharedRules::perPage(),
        ];
    }

    /**
     * Get the validated data with fields cast to their types.
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated();

        if (array_key_exists('read', $data)) {
            $data['read'] = filter_var($data['read'], FILTER_VALIDATE_BOOLEAN);
        }

        if (!is_null($key)) {
            return data_get($data, $key, $default);
        }

        return $data;
    }
}
