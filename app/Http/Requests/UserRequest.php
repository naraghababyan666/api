<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\GettingErrorMessages;

class UserRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['string', 'max:255'],
            'role_id' => ['required', 'integer',"exists:roles,id"],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'tax_identity_number' => ['regex:/^[0-9]{1,12}$/'],
            'avatar' => ['mimes:jpg,png', 'max:2048'],
        ];
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
