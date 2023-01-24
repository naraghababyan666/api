<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesLearning extends Model
{
    use HasFactory;
    public $table = 'courses_learning';
    protected $fillable = [
        'course_id',
        'user_id',
        'payment_id'
    ];

    public function course(){
        return $this->belongsTo(Course::class)->select(
            'id',
            'title',
            'sub_title',
            'cover_image',
            'price',
            'language',
            'type',
            'status',
            'level',
            'currency',
            'created_at',
            'trainer_id',
            'category_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function buyerData(){
        return $this->belongsTo(User::class, 'user_id', 'id')->select(['first_name', 'last_name', 'avatar']);
    }

    public static function sendCoursePassNotification($userId,$courseId){

        $learning =  CoursesLearning::query()->where("user_id",$userId)->where("course_id",$courseId)->first();
        $course = Course::query()->with(["sections","sections.quiz"])->find($courseId);
        $lessons = json_decode($learning->lessons_status,true);
        $isPass = null;
        $user = User::query()->find($userId);
        if(!empty($lessons)){
            $isPass = true;
            foreach ($lessons??[] as $lesson){
                if($lesson["status"]==0){
                    $isPass = false;
                    break;
                }
            }
//            dd($isPass);
        }

        foreach ($course->sections??[] as $section){
            foreach ($section->quiz??[] as $quiz){
                $quizAnswer = QuizeAnswer::query()->where("quiz_id",$quiz->id)->where("student_id",$userId)->first();
                if(is_null($quizAnswer)){
                    $isPass = false;
                    break;
                }else{
                    $isPass = true;
                }
            }
        }
        if($isPass){
            Notification::sendNotification($course->user_id, __("messages.course_passed"), __("messages.course_passed_message", ["user" => $user->first_name . " " . $user->last_name, "course" => $course->title]),"pass_course", 'course', $courseId);

        }

    }

}
