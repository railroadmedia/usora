<?php

namespace Railroad\Usora\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailChangeConfirmationRequest extends FormRequest
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
            'token' => [
                'bail',
                'required',
                'string',
                Rule::exists(
                    config('usora.database_connection_name') . '.' . config('usora.tables.email_changes'),
                    'token'
                ),
            ],
        ];
    }

    public function messages()
    {
        return [
            'token' => 'Invalid token.',
        ];
    }
}
