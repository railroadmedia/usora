<?php

namespace Railroad\Usora\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**  Form Request - extend the Laravel Form Request class and handle the validation errors messages
 *
 * Class FormRequest
 *
 * @package Railroad\Usora\Requests
 */
class FormRequest extends LaravelFormRequest
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

    /** Get the failed validation response in json format
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = [];

        foreach (
            $validator->errors()
                ->getMessages() as $key => $value
        ) {
            $errors[] = [
                "source" => $key,
                "detail" => $value[0],
            ];
        }

        throw new HttpResponseException(
            response()->json(
                [
                    'status' => 'error',
                    'code' => 422,
                    'total_results' => 0,
                    'results' => [],
                    'errors' => $errors,
                ],
                422
            )
        );
    }
}