<?php

namespace Railroad\Usora\Requests;

use Railroad\Usora\Requests\FormRequest;

class UserFieldJsonUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'numeric',
            'key' =>'string|max:255|min:1',
            'value' => 'nullable|string'
        ];
    }
}
