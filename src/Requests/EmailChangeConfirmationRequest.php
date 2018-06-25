<?php

namespace Railroad\Usora\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Railroad\Usora\Services\ConfigService;

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
                    ConfigService::$databaseConnectionName .
                    '.' .
                    ConfigService::$tableEmailChanges,
                    'token'
                ),
            ],
        ];
    }

    public function messages()
    {
        return [
            'token'  => 'Invalid token.',
        ];
    }
}
