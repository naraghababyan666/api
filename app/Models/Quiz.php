<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $table = 'quizzes';

    protected $fillable = [
        'section_id',
        'position',
        "title"
    ];

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function question(){
        return $this->hasMany(QuizQuestion::class,"quiz_id","id");
    }

    public static function calculateQuizResult($data)
    {
        $quizQuestionsCount = QuizQuestion::query()->where("quiz_id",$data['quiz_id'])->count();
        foreach ($data["answers"]??[] as $answer) {
            $question = QuizQuestion::query()->find($data['question_id']);
            if($question){

            }

        }


    }
}
