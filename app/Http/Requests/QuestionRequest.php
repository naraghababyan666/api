<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class QuestionRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'question' => ['required'],
            'answers' => ['required'],
            'right_answers' => ['required'],

        ];
        return $rules;

    }

    public function failedValidation(Validator $validator)
    {
        $messages = GettingErrorMessages::gettingMessage($validator->errors()->messages());

        throw new HttpResponseException(response()->json([

            'success' => false,
            'message' =>  __('messages.validation_errors'),
            'errors' => $messages
        ])->header('Status-Code', 200));

    }

}
