<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizQuestionController extends Controller
{



    public function store(QuestionRequest $request)
    {
        $data = $request->all();
        $validated = $request->validated();
        if(array_key_exists('quiz_id', $data)){
            $newQuiz = QuizQuestion::create([
                'quiz_id' => $data['quiz_id'],
                'question' => $validated['question'],
                'answers' => json_encode($validated['answers']),
                'right_answers' => json_encode($validated['right_answers']),
                'multiple_choice' => $data['multiple_choice']
            ]);
        }else{
            $quiz = Quiz::create([
                'title' => $data['title']??null,
                'section_id' => $data['section_id'],
                "position"=>$data['position']??0,
            ]);
            $newQuiz = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $validated['question'],
                'answers' => json_encode($validated['answers']),
                'right_answers' => json_encode($validated['right_answers']),
                'multiple_choice' => $data['multiple_choice']
            ]);
        }
        $newQuiz["answers"] = !empty($newQuiz["answers"])?json_decode($newQuiz["answers"],true):null;
        $newQuiz["right_answers"] = !empty($newQuiz["right_answers"])?json_decode($newQuiz["right_answers"],true):null;
        return response()->json(['success' => true,'data' => $newQuiz]);
    }

    public function updateQuizQuestion(QuestionRequest $request, $id){
        $question = QuizQuestion::find($id);
        if($question){
            $question->update([
                'quiz_id' => $request['quiz_id'],
                'question' => $request['question'],
                'answers' => json_encode($request['answers']),
                'right_answers' => json_encode($request['right_answers']),
                'multiple_choice' => $request['multiple_choice']
            ]);
            $question->save();
            return response()->json(['success' => true,'message' => __("messages.question-update")], 200);
        }
        return response()->json(['success' => false,'message' => __("messages.question-not-found")], 200);
    }

    public function getQuizQuestionById($id){
        $question = QuizQuestion::find($id);
        $question["answers"] = !empty($question["answers"])?json_decode($question["answers"],true):null;
        $question["right_answers"] = !empty($question["right_answers"])?json_decode($question["right_answers"],true):null;
        if($question){
            return response()->json(['success' => true,'data' => $question], 200);
        }
        return response()->json(['success' => false,'message' => __("messages.question-not-found")]);
    }

    public function deleteQuizQuestion($id){
        QuizQuestion::destroy($id);
        return response()->json(['success' => false,'message' => __('messages.question-delete')], 200);
    }

}
