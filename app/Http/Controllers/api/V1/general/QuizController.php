<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    public function getAllQuestions($id){
        $questions = QuizQuestion::where('quiz_id', $id)->get();
         foreach ($questions as $question){
             $question["answers"] = !empty($question["answers"])?json_decode($question["answers"],true):null;
             $question["right_answers"] = !empty($question["right_answers"])?json_decode($question["right_answers"],true):null;
         }
        return response()->json(['success' => true, 'data' => $questions], 200);
    }

    public function deleteQuiz($id){
        Quiz::destroy($id);
        return response()->json(['success' => true, 'message' => __("messages.quiz-delete")], 200);
    }

    public function updateQuiz(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_id' => ['integer', 'exists:sections,id'],
            'course_id' => ['integer', 'required', 'exists:courses,id'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 200)->header('Status-Code', '401');
        }
        if (isset($request['id'])) {
            $quiz = Quiz::find($request['id']);
            if(isset($request['section_id'])){
                $section = Section::query()->where("course_id",$request['course_id'])->where("id",$request['section_id'])->first();
            }
            if ($quiz ) {
                $quiz->section_id = $section->id ?? $quiz->section_id;
                $quiz->position = $request['position'] ?? $quiz->position;
                $quiz->title = $request['title'] ?? $quiz->title;
                $quiz->save();
                return response()->json(['success' => true, 'message' => __("messages.user-updated")], 200);
            }
        }
        return response()->json(['success' => false, 'message' => __("messages.not_found")], 200);
    }

}
