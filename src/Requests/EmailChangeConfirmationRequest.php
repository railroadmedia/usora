<?php

namespace Railroad\Usora\Requests;

use Illuminate\Database\Query\Builder;
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
                )->where(function (Builder $query) {
                    $query->whereRaw('created_at + INTERVAL ? HOUR >= CURRENT_TIMESTAMP', ConfigService::$emailChangeTtl);
                }),
            ]
        ];
    }
}
