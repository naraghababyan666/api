<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notification;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function setRateCourse(Request $request){
        $validator  = Validator::make($request->all(), [
            'course_id' => 'required',
            'rate' => 'required',
            'message' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "message" => __('messages.fail')
            ])->header('Status-Code', 200);
        }
        $data = $validator->validated();
        $rating = Review::where('user_id', Auth::id())->where('course_id', $data['course_id'])->first();
        $course = Course::find($data['course_id']);
        if(empty($rating)){
            $newReview = Review::create([
                'user_id' => Auth::id(),
                'course_id' => $data['course_id'],
                'rate' => $data['rate'],
                'message' => $data['message']??null
            ]);
            Notification::sendNotification($course->user_id, __("messages.new_review"), __("messages.course_review_message", ["user" => auth()->user()->first_name . " " . auth()->user()->last_name, "course" => $course->title]),"review", 'review', $newReview->id);
            return response()->json([
                'success' => true,
                'message' => __('messages.rate-added'),
            ], 200);
        }else{
            Review::where('user_id', Auth::id())->where('course_id', $data['course_id'])->update([
                'rate' => $data['rate'],
                'message' => $data['message']
            ]);
            Notification::sendNotification($course->user_id, __("messages.updated_review"), __("messages.updated_course_review_message", ["user" => auth()->user()->first_name . " " . auth()->user()->last_name, "course" => $course->title]),"review", 'review', $rating->id);

            return response()->json([
                'success' => true,
                'message' => __('messages.rate-update'),
            ], 200);
        }
    }

    public function removeRateCourse(Request $request){
        $validator  = Validator::make($request->all(), [
            'course_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $data = $validator->validated();
        Review::where('user_id', Auth::id())->where('course_id', $data['course_id'])->delete();
        return response()->json([
            'message' => __('messages.rate-delete'),
        ], 200);
    }

    public function deleteReviewById($id){
        $review = Review::query()->where('id', $id)->where('user_id', Auth::id())->first();
        if(is_null($review)){
            return response()->json(['success' => false, 'message' => __('messages.no-review')], 404);
        }
        $review->delete();
        return response()->json(['success' => true, 'message' => __('messages.review-deleted')]);
    }
}
