<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrainerMetaRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string',"nullable"],
            'last_name' => ['required', 'string',"nullable"],
            'headline' => ['string', 'min:5',"nullable"],
            'bio' => ['string', 'min:5',"nullable"],
            'avatar' => ['string',"nullable"],
            'tax_identity_number' => ['min:8',"nullable"],
            'company_name' => ['string',"nullable"]
        ];

    }

    public function failedValidation(Validator $validator)
    {
        $messages = GettingErrorMessages::gettingMessage($validator->errors()->messages());

        throw new HttpResponseException(response()->json([

            'success' => false,
            'message' => __('messages.validation_errors'),
            'errors' => $messages
        ])->header('Status-Code', 200));
    }
}
