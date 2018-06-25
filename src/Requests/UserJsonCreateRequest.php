<?php

namespace Railroad\Usora\Requests;

use Railroad\Usora\Requests\FormRequest;

class UserJsonCreateRequest extends FormRequest
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
            'email' => 'required|email',
            'display_name' =>'required|string|max:255|min:2',
            'password' => 'required|string'
        ];
    }
}
