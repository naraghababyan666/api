<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class LessonRequest extends FormRequest
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

            'title' => ['string', 'max:255','nullable'],
            'course_id' => ['required','integer'],
            'section_id' => ['required','integer'],
            'article' => ['string',"nullable"],
            'duration' => ['integer',"nullable",'size:10'],
            'video_url' => ['string', 'max:255','regex:/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i','nullable'],
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
