<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CourseRequest extends FormRequest
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

    public $model;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {


        $rules = [
            'type' => ['required'],
            'cover_image' => ["nullable"],
            'promo_video' => ["nullable"],
            'title' => ['string', 'max:255',"nullable"],
            'sub_title' => ['string', 'max:255',"nullable"],
            'language' => ["integer", "exists:languages,id","nullable"],
            'status' => ["integer","nullable"],
            'category_id' => ["integer", "exists:categories,id","nullable"],
            'max_participants' => ["integer","nullable"],
            'level' => ["integer","nullable"],
            'trainer_id' => ["integer", "exists:trainers,id","nullable"],
            'price' => ['regex:/^\d+(\.\d{1,2})?$/',"nullable"],
            'address' => ["nullable",'max:255'],
            'requirements' => ["array"],
            'link' => ['regex:/^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$/'],
            'will_learn' => ["array"],
            'currency' => ['string',"nullable"],
            'lessons' => ['array',"nullable"],
            'lessons.*.title' => ['string', 'max:255',"nullable"],
            "lessons.*.duration" => ["integer","nullable",'digits_between:1,10'],
            'lessons.*.start_time' => ["date_format:Y-m-d H:i:s","nullable"],
            'lessons.*.course_id' => [ "integer","nullable"],
            'trainer.first_name' => [ 'string', 'max:255',"nullable"],
            'trainer.last_name' => [ 'string', 'max:255',"nullable"],
            'trainer.bio' => ['string',"nullable"],
            'trainer.avatar' => [ 'string',"nullable"],
        ];
        $data = $this->request->all();
        if(isset($data["id"])){
            $model = Course::query()->with(["sections","sections.lessons","sections.quiz"])->find($data["id"]);
        }else{
            $model = new Course();
        }
        $this->model = $model;



        if (!empty($data["status"]) && $data["status"] == Course::UNDER_REVIEW) {
            foreach ($rules as $key => $value) {
             if($model->type != Course::ONLINE_WEBINAR && $key == 'link'){
                    continue;
             }
            if($model->type != Course::OFFLINE && $key == 'address'){
                continue;
            }

            if($model->type == Course::ONLINE){
                if($key == 'max_participants' || $key == "lessons.*.start_time"){
                    continue;
                }
            }

            if($model->trainer_id > 0 ){
                continue;
            }
            if (auth()->user()->role_id == Role::TRAINER) {
                    if ($key == "trainer" || $key == "trainer_id"  || $key == "price" || $key == "max_participants" || str_contains($key, "lesson") || str_contains($key, "trainer")) {
                        continue;
                    }
            }
            if ($key == "type" || $key == "promo_video" || $key == "will_learn" || $key == "requirements" ) {
                continue;
            }
            $rules[$key][] = "required";
            }
        }
        return $rules;
    }

    public function failedValidation(Validator $validator)
    {
        $messages = GettingErrorMessages::gettingMessage($validator->errors()->messages());
        $completed_steps = Course::getCompletedSteps($this->model);
        throw new HttpResponseException(response()->json([

            'success' => false,
            'message' =>  __('messages.validation_errors'),
            'errors' => $messages,
            'completed_steps'=>$completed_steps
        ])->header('Status-Code', 200));
    }
}
