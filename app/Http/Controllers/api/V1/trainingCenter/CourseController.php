<?php

namespace App\Http\Controllers\api\V1\trainingCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Http\Resources\V1\CourseResource;
use App\Http\Traits\ApiResponseHelpers;
use App\Models\BasketList;
use App\Models\Category;
use App\Models\Course;
use App\Models\CoursesLearning;
use App\Models\History;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\Resource;
use App\Models\Review;
use App\Models\Role;
use App\Models\Section;
use App\Models\StudentCourses;
use App\Models\Subscription;
use App\Models\Trainer;
use App\Models\User;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Certificates;
use App\Models\QuizeAnswer;
use App\Models\QuizQuestion;

class CourseController extends Controller
{
    use ApiResponseHelpers;
    public function createCourse(CourseRequest $request)
    {
        if (User::isModerator(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => __("messages.forbidden"),
            ], 403)->header('Status-Code', 403);
        }

            $model = new Course();
            $model->user_id = auth()->id();
            $hasType = true;
            if ($hasType) {
                $model->type = $request['type'];
                $model->status = Course::DRAFT;
                $model->save();
                if ($request['type'] == Course::ONLINE) {
                    Section::create([
                        "title" => "Section 1",
                        "course_id" => $model->id,
                    ]);
                }
                return response()->json(['success' => true,
                    'data'=>new CourseResource($model)], 200);
            }
            return response()->json([
                'success' => false,
                'message' => __('messages.type-not-found'),
            ])->header('Status-Code', 200);


    }

    public function getTrainerCourses($id)
    {
        $user = User::where("id", $id)->first();
//        if ($user && $user->role_id == Role::TRAINER) {
            $courses = Course::where('user_id', $user->id)->where('status', 3)->get();
            Category::$language =$this->language_id;
            foreach ($courses as $course) {
                if (!empty($course->category_id) ) {
                    $data = Category::with(["translation", "parent"])->find($course->category_id)->toArray();
                    $course["names"] =array_reverse( Course::getNamesArray($data));
                }
            }
            if (count($courses) == 0) {
                return response()->json(['success' => false,
                    'message' => __("messages.trainer-have-not-course")], 200);
            } else {
                return response()->json(['success' => true, 'data' => $courses], 200);
            }
//        } else {
//            return response()->json(['success' => false, 'data' => __('messages.not_found')], 200);
//        }
    }

    public function getUserReview($id){
        $reviews = [];
        $courses = Course::query();
        $courses->where("user_id", $id)->with('rates');
        $courses = $courses->get();
        if(count($courses) != 0){
            //10 /10
            foreach ($courses as $item){
                if(count($item['rates']) != 0){
                    foreach ($item['rates'] as $rate){
                        $user = User::where('id', $rate['user_id'])->first();
                        $reviews[] = [
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                            'avatar' => !is_null($user['avatar']) ? env("APP_URL") . "/" . $user['avatar'] : null,
                            'course_id' => $rate['course_id'],
                            'date' => $rate['date'],
                            'rate' => $rate['rate'],
                            'message' => $rate['message']
                        ];
                    }
                }
            }
            return response()->json(['success' => true, 'data' => $reviews]);
        }else{
            return response()->json(['success' => false, 'message' => __('messages.trainer-have-not-course')]);
        }
    }

    public function updateCourse(CourseRequest $request)
    {
        $isModerator = User::isModerator(auth()->id());
//        try {
          $with =["lessons", "trainer"];
            if($request["type"] == Course::ONLINE){
                $with = ["sections","sections.lessons","sections.quiz","trainer"];
            }
            $model = Course::query()->with($with)->find($request["id"]);
            if (!empty($model)) {
                $userCreated = User::query()->where('id', $model['user_id'])->first();
                $userName = $userCreated['first_name'] . ' ' .$userCreated['last_name'];

                $isSlug =  !empty( $request["title"]) && $model->title != $request["title"];

                foreach ($request->toArray() as $key => $value) {
                    if ($key == "lessons" || $key == "trainer"|| $key == "type" || $key == 'language_code' ) {
                        continue;
                    }
                    if ($key == "status") {

                        if ($value == Course::UNDER_REVIEW) {
                            $moderators = User::query()->where('role_id', 2)->get();
                            if(isset($request['title'])){
                                $model->title = $request->title;
                                $model->save();
                            }
                            foreach ($moderators as $m) {
                                Notification::create([
                                    "user_id" => $m->id,
                                    "title" => __("messages.received_moderator_data",['user' => $userName, 'course_title' => $model['title']]),
                                    "message" => __("messages.received_moderator_data", ['user' => $userName, 'course_title' => $model['title']]),
                                    "status" => 0,
                                    "item" => 'course',
                                    "item_id" => $model->id,
                                    "type" => "under_review",
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                            $model->status = $value;

                        } elseif ($value != Course::APPROVED) {
                            $model->status = $value;
                            Subscription::sendEmailAboutNewCourse($model);

                        }
                    }
                    else {
                        if( $key == "requirements"|| $key == "will_learn"){
                            $model->$key = json_encode($value);
                        }else{
                            $model->$key = $value;
                        }

                    }
                }
                if(isset($request['certificate'])){
                    if($request['certificate'] == 1){
                        $model->certificate = 1;
                    }else{
                        $model->certificate = 0;
                    }
                }
                if (!empty($request["lessons"]) && ($request["type"] == Course::ONLINE_WEBINAR || $request["type"] == Course::OFFLINE)) {

                        try {
                            Lesson::query()->where("course_id",$model->id)->delete();
                            foreach ($request["lessons"] as $lesson) {
                                if(isset($lesson['start_time'])){
                                    $lesson["title"]=$lesson["title"]??"";
                                    Lesson::create($lesson);
                                }else{
                                    return response()->json(['success' => false, 'message' => __('messages.start-time-no')]);
                                }

                            }
                        } catch (\Exception $e) {
                            throw new HttpResponseException(response()->json([
                                'success' => false,
                                'message' => $e->getMessage(),
                            ], $e->getCode())->header('Status-Code', $e->getCode()));
                        }

                }
                if (!empty($request["trainer"])) {

                    $trainer_id = $this->createTrainer($request["trainer"]);
                    $model->trainer_id = $trainer_id;
                }
                if ($model->slug && $isSlug) {
                    $model->slug = SlugService::createSlug(Course::class, 'slug', $request['title']);
                }
                //for now lest keep all courses published
                if(!empty($request["status"]) && $request["status"]==Course::APPROVED){
                    $model->status = Course::APPROVED;
                    Subscription::sendEmailAboutNewCourse($model);
                }
                $model->save();

                $model = Course::query()->with($with)->find($request["id"]);
                $model['completed_steps'] = Course::getCompletedSteps($model);
                $model['creation_percent'] = Course::getCompletedSteps($model,true);
                return response()->json(['success' => true,
                    'data'=>new CourseResource($model)], 200);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => __("messages.not_found"),
                ], 404)->header('Status-Code', 404);
            }

    }

    public function getCourses(Request $request)
    {
        $courses = Course::query()->with(["lessons", "trainer", 'rates'])->where('status', Course::APPROVED);
        $limit = $request["limit"] ?? 10;
        if (isset($request["category_id"])) {
            $courses->where("category_id", $request["category_id"]);
        }
        if (isset($request["language_code"])) {
            $courses->where("language", $this->language_id);
        }
        $courses = $courses->paginate($limit);
//        foreach ($courses as $course){
//            $course->cover_image = $course->cover_image?env("APP_URL")."/".$course->cover_image:null;
//        }
        if (count($courses) == 0) {
            $data = [
                'success' => false,
                'data' => __('messages.not_found'),
            ];
            return response($data)
                ->setStatusCode(200)->header('Status-Code', '200');
        }
//        return response(new CourseResource($courses))
//            ->setStatusCode(200)->header('Status-Code', '200');
        return $this->respondWithSuccess(new CourseResource($courses));


    }

    public function relatedCourses($id){
        $course = Course::query()->with('trainer')->find($id);
        if(is_null($course)){
            return response()->json(['success' => false, 'message' => __('messages.not_found')]);
        }
        $categories = Category::with(["translation", "parent"])->find($course->category_id)->toArray();
        $categoryIds['child_category'] = $categories['id'];
        if(!is_null($categories['parent'])){
            $categoryIds['parent_category'] = $categories['parent']['id'];
        }
        $coursesCurrentTrainer = [];
        if(!is_null($course['trainer'])){
            $coursesCurrentTrainer[] = Course::query()->where('trainer_id', $course['trainer']['id'])->get();
        }
        $coursesCurrentCategories = [];
        $coursesCurrentCategories[] = Course::query()->where('category_id', $categoryIds['child_category'])->get();
        $coursesCurrentCategories[] = Course::query()->where('category_id', $categoryIds['parent_category'])->get();

        return response()->json(['a' => $coursesCurrentCategories, 'b' => $coursesCurrentTrainer]);
    }

    public function courseByIdForPreview($id, Request $request){
        $courseStatus = [1 => 'Draft', 2 => 'Under review', 3 => 'Approved', 4 => 'Declined', 5 => 'Deleted'];
        $courseLanguage = [1 => 'Armenian', 2 => 'English'];
        $courseLevel = [1 => 'All levels', 2 => 'Beginners', 3 => 'Middle level', 4 => 'Advanced'];
        $token = $request->bearerToken();
        $user_id = null;
        if (!empty($token)) {
            $user = auth('sanctum')->user();
            if(!is_null($user)){
                $user_id = $user->getAuthIdentifier();
            }else{
                return response()->json(['success' => false, 'message' => __('messages.user_not_found')]);
            }
        }

        $courses = Course::query()->with(["lessons"])->where("id",$id)->first();
        $with = ["lessons","user"];
        if ( is_null($courses)) {
            $data = [
                'success' => false,
                'data' => __('messages.not_found'),
            ];
            return response($data)
                ->setStatusCode(404)->header('Status-Code', "404");

        }
        if ($courses->type == Course::ONLINE) {
            $with[] = "sections.lessons";
            $with[] = "sections";
            $with[] = "sections.quiz";
            $with[]="sections.quiz.question";
        }
        $courses = Course::query()->with($with)->find($id);
        if (!is_null($courses)) {

                if (!empty($courses->category_id)) {
                    Category::$language = $this->language_id;
                    $data = Category::with(["translation", "parent"])->find($courses->category_id)->toArray();
                    $courses["categories"] = array_reverse(Course::getNamesArray($data));
                }
                $courses['type_id'] = $courses['type'];
                $courses['status'] = __('messages.status-'.$courseStatus[$courses['status']]);
                $courses['language'] = !empty($courses['language']) ? __('messages.'.$courseLanguage[$courses['language']]) : __('messages.Armenian');
                $courses['level'] = !empty($courses['level']) ? __('messages.level-'.$courseLevel[$courses['level']]) : __('messages.level-Beginners');
                $trainerInfo = [];
                if ($courses['trainer_id']) {
                    $trainerInfo = Trainer::query()->where("id", $courses['trainer_id'])->select(["id", "first_name", "last_name", "avatar", 'bio'])->first();
                }
                $trainer["id"] = $courses['user_id'];
                $trainer["first_name"] = isset($trainerInfo['first_name']) ?$trainerInfo['first_name']: $courses["user"]["first_name"] ?? null;
                $trainer["last_name"] = isset($trainerInfo['last_name']) ?$trainerInfo['last_name']: $courses["user"]["last_name"] ?? null;
                $trainer["bio"] = isset($trainerInfo['bio']) ?$trainerInfo['bio']: $courses["user"]["bio"] ?? null;
                $trainer["company_avatar"] = !empty($courses['user']["avatar"]) ? env("APP_URL") . "/" . $courses['user']["avatar"] : null;
                $trainer["avatar"] = !empty($trainerInfo["avatar"])? $trainerInfo["avatar"]:$trainer["company_avatar"];
                $trainer["company_name"] = $courses["user"]['company_name'] ?: null;
                if(!is_null($user_id)){
                    $in_basket = BasketList::query()->where('user_id', $user_id)->where('course_id', $id)->first();
                    if(!is_null($in_basket)){
                        $courses['in_basket'] = 1;
                    }else{
                        $courses['in_basket'] = 0;
                    }
                }
                $courses["trainer"] = $trainer;
                unset($courses['trainer_id']);
                if ($courses['type'] == Course::OFFLINE) {
                    $courses['type'] = __('messages.type-offline');
                } elseif ($courses['type'] == Course::ONLINE) {
                    $courses['type'] = __('messages.type-online');
                    $quizCount = Quiz::query()->leftJoin("sections", "section_id", "=", "sections.id")->where("course_id", '=', $id)->count();
                    $lessonCount = Lesson::query()->where('course_id', '=', $courses['id'])->count();
                    $courses['lessons_count'] = $lessonCount;
                    $courses['quiz_count'] = $quizCount;
                    unset($courses['link']);
                } elseif ($courses['type'] == Course::ONLINE_WEBINAR) {
                    $courses['type'] = __('messages.type-online-webinar');
                }
                unset($courses['user']);
                $sections = [];
                $access = $full_access = true;
                $course_lessons_passed = 0;
                if($courses['type_id'] == Course::ONLINE){
                    if(!empty($courses->sections)){


                        if($access && !isset($request['student_id'])){
                            $full_access = true;
                        }else{
                            $full_access = true;
                        }

                        $course_lessons_passed = 1;
                        foreach($courses->sections as $key=>$val){
                            $s_data = ['title'=>$val->title,'lessons'=>[]];
                            if(!empty($val->lessons)){
                                $lessons_info = [];
                                foreach($val->lessons as $l_k=>$l_v){
                                    $l_st = 0;
                                    if(!empty($lessons_statuses)){
                                        foreach($lessons_statuses as $st){
                                            if($st['id'] ==$l_v->id ){
                                                $l_st = $st['status'];
                                                break;
                                            }
                                        }
                                    }

                                    if(!empty(trim($l_v->video_url))){
                                        $type = 'video';
                                    }else{
                                        $type= 'article';
                                    }
                                    $l_info = [
                                        'title'=>$l_v->title,
                                        'type'=>$type,
                                        'description'=>$l_v->description,
                                        'questions_count'=>0,
                                        'video_url'=>'',
                                        'article'=>'',
                                        'questions'=>[],
                                        'resources'=>[],
                                        'resources_count'=>count($l_v->resources),
                                        'passed'=>$l_st,
                                        'id'=>$l_v->id,
                                    ];
                                    if(!$l_st){
                                        $course_lessons_passed = 0;
                                    }
                                    if($access){
                                        $resourcesArr = [];
                                        if(!empty($l_v->resources)){
                                            foreach($l_v->resources as $resource){
                                                $resourcesArr[] = [
                                                    'id'=>$resource->id,
                                                    'title'=>$resource->title,
                                                    'path'=>$resource->path,
                                                ];

                                            }
                                        }
                                        $l_info['article'] = $l_v->article;
                                        $l_info['video_url'] = $l_v->video_url;
                                        $l_info['resources'] = $resourcesArr;

                                    }
                                    $lessons_info[$l_v->position]=$l_info;

                                }
                                $s_data['lessons'] = $lessons_info;
                            }

                            if(!empty($val->quiz)){
                                $total_points = 0;
                                foreach($val->quiz as $q_key=>$q_val){
                                    $qi = 0;
                                    foreach($q_val->question as $question_i){
                                        $qi++;
                                    }
                                    $questionS = [];
                                    $total_data = '';
                                    if($access){
                                        $total_points  = $right_answer = 0;
                                        foreach($q_val->question as $key=>$val){
                                            $quize_answers = [];

                                            $passedQuize = QuizeAnswer::query()->where("question_id",$val->id)
                                                ->where('student_id',$user_id)->first();
                                            if($passedQuize){
                                                $your_answer = json_decode($passedQuize->answer,true);

                                                $right_answers = json_decode($val->right_answers,true);

                                                $point = 1;
                                                $total_points += 1;
                                                if($your_answer == $right_answers){
                                                    $right_answer += $point;
                                                }

                                                $quize_answers = [
                                                    'right_answers'=>$right_answers,
                                                    'your_answer'=>$your_answer,

                                                ];
                                            }
                                            if($quize_answers == []){
                                                $quize_answers = null;
                                            }
                                            $res = [
                                                'question_id'=>$val->id,
                                                'title'=>$val->question,
                                                'answers'=>json_decode($val->answers,true),
                                                'multiple_choice'=>$val->multiple_choice,
                                                'your_answers'=>$quize_answers
                                            ];
                                            $questionS[]=$res;
                                        }
                                        if($total_points != 0){
                                            $total_data = round($right_answer*100/$total_points);
                                        }
                                    }
                                    //check if quize passed or no
                                    $passedQuize = QuizeAnswer::query()->where("quiz_id",$q_val->id)
                                        ->where('student_id',$user_id)->first();
                                    if(!$passedQuize){
                                        $course_lessons_passed = 0;
                                    }
                                    $s_data['lessons'][$q_val->position]=[
                                        'id'=>$q_val->id,
                                        'type'=>'quiz',
                                        'description'=>'',
                                        'title'=>$q_val->title,
                                        'questions_count'=>$qi,
                                        'video_url'=>'',
                                        'article'=>'',
                                        'questions'=>$questionS,
                                        'resources'=>[],
                                        'resources_count'=>0,
                                        'total_percents'=>$total_data
                                    ];
                                }
                            }

                            ksort($s_data['lessons']);

                            $sections[] = $s_data;

                        }
                    }
                    unset($courses->sections);
                    unset($courses->lessons);
                    $courses->sections = $sections;

                }
                $courses->full_access = $full_access;
                $courses->course_passed = $course_lessons_passed;
                $data = [
                    'success' => true,
                    'data' => new CourseResource($courses),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');


        }

        return $this->respondWithSuccess(new CourseResource($courses));
    }
    public function getUserCourses(Request $request)
    {
        $courses = Course::query()
            ->with(["lessons", "trainer", 'rates'])
            ->where('user_id', auth()->id())
            ->whereNot("status",Course::DELETED)
            ->orderBy("created_at","desc");
        $limit = $request["limit"] ?? 10;
        if(isset($request->status) && $request->status > 0){
            $courses->where('status', $request->status);
        }

        $courses = $courses->paginate($limit);
        foreach($courses as $element){
            $element->completed_percent =round(Course::getCompletedSteps($element, true));
        }
        return $this->respondWithSuccess($courses);



    }

    public function getReviewsByCourseId($id){
        $reviews = Review::query()->where('course_id', $id)->with('user')->orderBy("updated_at","DESC")->get();
        foreach ($reviews as $review){
            $review['user']['avatar'] = !is_null($review['user']['avatar']) ? env("APP_URL") . "/" . $review['user']['avatar'] : null;
        }
        foreach ($reviews as $review){
            $review['first_name'] = $review['user']['first_name'];
            $review['last_name'] = $review['user']['last_name'];
            $review['user_id'] = $review['user']['id'];
            $review['avatar'] = $review['user']['avatar'];
            unset($review['user']);
        }

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function courseByIdForGuest($id, Request $request){
        $courseStatus = [1 => 'Draft', 2 => 'Under review', 3 => 'Approved', 4 => 'Declined', 5 => 'Deleted'];
        $courseLanguage = [1 => 'Armenian', 2 => 'English'];
        $courseLevel = [1 => 'All levels', 2 => 'Beginners', 3 => 'Middle level', 4 => 'Advanced'];
        $token = $request->bearerToken();
        $user_id = null;
        if (!empty($token)) {
            $user = auth('sanctum')->user();
            if(!is_null($user)){
                $user_id = $user->getAuthIdentifier();
            }else{
                return response()->json(['success' => false, 'message' => __('messages.user_not_found')]);
            }
        }
        if($request->student_id){
            $user_id = $request->user_id;
        }
        $courses = Course::query()->with(["lessons"])->where("id",$id)->where("status",Course::APPROVED)->first();
        $with = ["lessons","user"];
        if ( is_null($courses)) {
            $data = [
                'success' => false,
                'data' => __('messages.not_found'),
            ];
            return response($data)
                ->setStatusCode(404)->header('Status-Code', "404");

        }
        if ($courses->type == Course::ONLINE) {
            $with[] = "sections.lessons";
            $with[] = "sections";
            $with[] = "sections.quiz";
            $with[]="sections.quiz.question";
        }
        $courses = Course::query()->with($with)->find($id);
        if (!is_null($courses)) {
            if ($courses['status'] != Course::APPROVED) {
                return response()->json(['success' => false, 'message' => __('messages.not-approved-course')])
                    ->setStatusCode(404)->header('Status-Code', 404);;
            } else {
                if (!empty($courses->category_id)) {
                    Category::$language = $this->language_id;
                    $data = Category::with(["translation", "parent"])->find($courses->category_id)->toArray();
                    $courses["categories"] = array_reverse(Course::getNamesArray($data));
                }
                $courses['type_id'] = $courses['type'];
                $courses['status'] = __('messages.status-'.$courseStatus[$courses['status']]);
                $courses['language'] = !empty($courses['language']) ? __('messages.'.$courseLanguage[$courses['language']]) : __('messages.Armenian');
                $courses['level'] = !empty($courses['level']) ? __('messages.level-'.$courseLevel[$courses['level']]) : __('messages.level-Beginners');
                $trainerInfo = [];
                if ($courses['trainer_id']) {
                    $trainerInfo = Trainer::query()->where("id", $courses['trainer_id'])->select(["id", "first_name", "last_name", "avatar", 'bio'])->first();
                }
                $trainer["id"] = $courses['user_id'];
                $trainer["first_name"] = isset($trainerInfo['first_name']) ?$trainerInfo['first_name']: $courses["user"]["first_name"] ?? null;
                $trainer["last_name"] = isset($trainerInfo['last_name']) ?$trainerInfo['last_name']: $courses["user"]["last_name"] ?? null;
                $trainer["bio"] = isset($trainerInfo['bio']) ?$trainerInfo['bio']: $courses["user"]["bio"] ?? null;
                $trainer["company_avatar"] = !empty($courses['user']["avatar"]) ? env("APP_URL") . "/" . $courses['user']["avatar"] : null;
                $trainer["avatar"] = !empty($trainerInfo["avatar"])? $trainerInfo["avatar"]:$trainer["company_avatar"];
                $trainer["company_name"] = $courses["user"]['company_name'] ?: null;
                if(!is_null($user_id)){
                    $in_basket = BasketList::query()->where('user_id', $user_id)->where('course_id', $id)->first();
                    if(!is_null($in_basket)){
                        $courses['in_basket'] = 1;
                    }else{
                        $courses['in_basket'] = 0;
                    }
                }
                $courses["trainer"] = $trainer;
                unset($courses['trainer_id']);
                if ($courses['type'] == Course::OFFLINE) {
                    $courses['type'] = __('messages.type-offline');
                } elseif ($courses['type'] == Course::ONLINE) {
                    $courses['type'] = __('messages.type-online');
                    $quizCount = Quiz::query()->leftJoin("sections", "section_id", "=", "sections.id")->where("course_id", '=', $id)->count();
                    $lessonCount = Lesson::query()->where('course_id', '=', $courses['id'])->count();
                    $courses['lessons_count'] = $lessonCount;
                    $courses['quiz_count'] = $quizCount;
                    unset($courses['link']);
                } elseif ($courses['type'] == Course::ONLINE_WEBINAR) {
                    $courses['type'] = __('messages.type-online-webinar');
                }
                unset($courses['user']);
                $sections = [];
                $access = $full_access = false;
                $course_lessons_passed = 0;
                $lesson_passed = '0%';
                if($courses['type_id'] == Course::ONLINE){
                    if(!empty($courses->sections)){
                        $lesson_passed = $courses->calculatePassedNumbers($user_id);
                        //if suer logged in and course is free
                        if(isset($request['student_id']) && $request['student_id'] > 0){
                            $studentCourse = CoursesLearning::where('user_id',$request['student_id'])
                                ->where('course_id',$courses->id)->first();
                        }else{

                            $studentCourse = CoursesLearning::where('user_id',$user_id)
                                ->where('course_id',$courses->id)->first();
                        }
                        $lessons_statuses = [];
                        if($studentCourse){
                            $access = true;
                            if(!empty($studentCourse->lessons_status)){
                                $lessons_statuses = json_decode($studentCourse->lessons_status,true);

                            }
                        }
                        if($access && !isset($request['student_id'])){
                            $full_access = true;
                        }else{
                            $full_access = false;
                        }

                        $course_lessons_passed = 1;

                        foreach($courses->sections as $key=>$val){
                            $s_data = ['title'=>$val->title,'lessons'=>[]];
                            if(!empty($val->lessons)){
                                $lessons_info = [];
                                foreach($val->lessons as $l_k=>$l_v){
                                   $l_st = 0;
                                   if(!empty($lessons_statuses)){
                                       foreach($lessons_statuses as $st){
                                           if($st['id'] ==$l_v->id ){
                                               $l_st = $st['status'];
                                               break;
                                           }
                                       }
                                   }

                                    if(!empty(trim($l_v->video_url))){
                                        $type = 'video';
                                    }else{
                                        $type= 'article';
                                    }
                                    $l_info = [
                                        'title'=>$l_v->title,
                                        'type'=>$type,
                                        'description'=>$l_v->description,
                                        'questions_count'=>0,
                                        'video_url'=>'',
                                        'article'=>'',
                                        'questions'=>[],
                                        'resources'=>[],
                                        'resources_count'=>count($l_v->resources),
                                        'passed'=>$l_st,
                                        'id'=>$l_v->id,
                                    ];
                                    if(!$l_st){
                                        $course_lessons_passed = 0;
                                    }
                                    if($access){
                                        $resourcesArr = [];
                                        if(!empty($l_v->resources)){
                                            foreach($l_v->resources as $resource){
                                                $resourcesArr[] = [
                                                    'id'=>$resource->id,
                                                    'title'=>$resource->title,
                                                    'path'=>$resource->path,
                                                ];

                                            }
                                        }
                                        $l_info['article'] = $l_v->article;
                                        $l_info['video_url'] = $l_v->video_url;
                                        $l_info['resources'] = $resourcesArr;

                                    }
                                    $lessons_info[$l_v->position]=$l_info;

                                }
                                $s_data['lessons'] = $lessons_info;
                            }

                            if(!empty($val->quiz)){
                                foreach($val->quiz as $q_key=>$q_val){
                                    $qi = 0;
                                    foreach($q_val->question as $question_i){
                                        $qi++;
                                    }
                                    $questionS = [];
                                    $total_data = '';
                                    if($access){
                                        $total_points  = $right_answer = 0;
                                        foreach($q_val->question as $key=>$val){
                                            $quize_answers = [];

                                            $passedQuize = QuizeAnswer::query()->where("question_id",$val->id)
                                                ->where('student_id',$user_id)->first();
                                            if($passedQuize){
                                                $your_answer = json_decode($passedQuize->answer,true);

                                                $right_answers = json_decode($val->right_answers,true);

                                                $point = 1;
                                                $total_points += 1;
                                                if($your_answer == $right_answers){
                                                    $right_answer += $point;
                                                }

                                                $quize_answers = [
                                                    'right_answers'=>$right_answers,
                                                    'your_answer'=>$your_answer,

                                                ];
                                            }
                                            if($quize_answers == []){
                                                $quize_answers = null;
                                            }
                                            $res = [
                                                'question_id'=>$val->id,
                                                'title'=>$val->question,
                                                'answers'=>json_decode($val->answers,true),
                                                'multiple_choice'=>$val->multiple_choice,
                                                'your_answers'=>$quize_answers
                                            ];
                                            $questionS[]=$res;
                                        }
                                        if($total_points != 0){
                                            $total_data = round($right_answer*100/$total_points);
                                        }
                                    }
                                    //check if quize passed or no
                                    $passedQuize = QuizeAnswer::query()->where("quiz_id",$q_val->id)
                                        ->where('student_id',$user_id)->first();
                                    if(!$passedQuize){
                                        $course_lessons_passed = 0;
                                    }
                                    $s_data['lessons'][$q_val->position]=[
                                        'id'=>$q_val->id,
                                        'type'=>'quiz',
                                        'description'=>'',
                                        'title'=>$q_val->title,
                                        'questions_count'=>$qi,
                                        'video_url'=>'',
                                        'article'=>'',
                                        'questions'=>$questionS,
                                        'resources'=>[],
                                        'resources_count'=>0,
                                        'total_percents'=>$total_data
                                    ];
                                }
                            }

                            ksort($s_data['lessons']);

                            $sections[] = $s_data;

                        }
                    }
                    unset($courses->sections);
                    unset($courses->lessons);
                    $courses->sections = $sections;

                }
                $courses->lessons_passed = $lesson_passed;
                $courses->full_access = $full_access;
                $courses->course_passed = $course_lessons_passed;
                $data = [
                    'success' => true,
                    'data' => new CourseResource($courses),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            }
        }

        return $this->respondWithSuccess(new CourseResource($courses));
    }

    public function getFirstLessonDate($course){
        dd($course);
    }

    public function getCourse($id)
    {
        $courses = Course::query()->where("id",$id)->where("user_id",auth()->id())->first();
        if (!is_null($courses)) {
            $with =["lessons", "trainer"];
            if($courses->type == Course::ONLINE){
                $with = ["sections","sections.lessons.lesson","sections.lessons.lesson.resources.resource","sections.quiz","sections.quiz.question","trainer"];
            }
            $courses = Course::query()->with($with)->find($id);
            $courses["completed_steps"] = Course::getCompletedSteps($courses);
            $courses["creation_percent"] = Course::getCompletedSteps($courses,true);

            unset($courses['user']);
            return $this->respondWithSuccess(new CourseResource($courses));
        }
        $data = [
            'success' => false,
            'messages' => __('messages.not_found'),
        ];
        return response($data)
            ->setStatusCode(404)->header('Status-Code', '404');

    }

    public function deleteCourse($id)
    {
        if ($id) {
            $query = Course::query();
            if ($query) {
                $course = $query->where('id', $id)->first();
                if ($course != null) {
                    if ($course['user_id'] === Auth::id()) {
                        $course->status = Course::DELETED;
                        $course->save();
                        $data = [
                            'status' => 'success',
                            'message' => __("messages.course_delete"),
                        ];
                        return response($data)
                            ->setStatusCode(200)->header('Status-Code', '200');
                    } else {
                        $data = [
                            'success' => false,
                            'message' => __("messages.forbidden"),
                        ];
                        return response($data)
                            ->setStatusCode(200)->header('Status-Code', '200');
                    }
                } else {
                    $data = [
                        'success' => false,
                        'message' => __("messages.not_found"),
                    ];
                    return response($data)
                        ->setStatusCode(200)->header('Status-Code', '200');
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __('messages.course-not-found'),
            ], 200)->header('Status-Code', '200');
        }
    }

    public function createSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => ['integer', 'required', 'exists:courses,id'],
            'title' => ['string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 200)->header('Status-Code', '200');
        }
        try {

            $section = Section::create([
                "title" => $request["title"] ?? "Section title",
                "course_id" => $request['course_id']
            ]);
            return response($section)->setStatusCode(200)->header('Status-Code', '200');

        } catch
        (\Exception $e) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500)->header('Status-Code', 401));
        }
    }

    public function updateSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['integer', 'required', 'exists:sections,id'],
            'title' => ['string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 200)->header('Status-Code', '200');
        }
        $section = Section::query()->find($request["id"]);
        try {
            $section->title = $request["title"];
            $section->save();
            return response($section)->setStatusCode(200)->header('Status-Code', '200');

        } catch
        (\Exception $e) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500)->header('Status-Code', 500));
        }
    }

    public function deleteSection(Request $request)
    {
        if (!empty($request["id"])) {
            $section = Section::query()->find($request["id"]);
            if ($section) {
                $course = Course::where('id', $section['course_id'])->where('user_id', Auth::id())->first();
                if ($course) {
                    $section->delete();
                    $data = [
                        'success' => true,
                        'message' => __("messages.deleted"),
                    ];
                    return response($data)
                        ->setStatusCode(200)->header('Status-Code', '200');
                } else {
                    dd(1);
                }

            } else {
                return response()->json([
                    'success' => false,
                    'message' =>  __("messages.not_found"),
                ], 200)->header('Status-Code', '200');
            }
        }
        return response()->json(['success' => false, 'message' => __('messages.insert-id')]);
    }

    private function createTrainer($data)
    {
        try {
            if(!empty($data["id"])){
                $trainer = Trainer::query()->find($data["id"]);
            }else{
                $trainer = new Trainer();
            }
            $trainer->first_name = $data["first_name"] ?? null;
            $trainer->last_name = $data["last_name"] ?? null;
            $trainer->bio = $data["bio"] ?? null;
            $trainer->user_id = auth()->id();
            $trainer->avatar = $data["avatar"] ?? null;

            $trainer->save();
            return $trainer->id;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code', 401);
        }
    }

    private function saveCourseHistory($id)
    {
        $model = Course::query()->with(["lessons", "trainer"])->find($id);
        $history = History::query()->where("course_id", $id)->where("user_id", auth()->id())->first();
        if ($history) {
            $history->course_id = $id;
            $history->user_id->auth()->id();
            $history->old_value->json_encode($model);
        } else {
            History::create([
                "course_id" => $id,
                "user_id" => auth()->id(),
                "old_value" => json_encode($model)
            ]);
        }
    }

    public function getMyLearningCourses(){
        $courses = CoursesLearning::query()->where('user_id', Auth::id())
            ->with(['course', 'course.rates'])->get();
        if(count($courses) != 0){
            foreach ($courses as $item) {
                if (!empty($item->course->category_id) ) {
                    Category::$language = $this->language_id;
                    $data = Category::with(["translation", "parent"])->find($item->course->category_id)->toArray();
                    $item->course["categories"] = array_reverse( Course::getNamesArray($data));
                }
                if(in_array($item['course']['type'], [2,3])){
                    if($item['course']['last_lesson_date'] > Carbon::now()){
                        $item->expired = false;
                    }else{
                        $item->expired = true;
                    }
                }

            }
            return response()->json(['success' => true, 'data' => $courses]);
        }
        return response()->json(['success' => true, 'message' => __('messages.not_found')]);

    }

    public function setLessonPass(Request $request){
        $user_id = Auth::id();

        if(isset($request['course_id'])){
            $model = CoursesLearning::query()->where('user_id', $user_id)
                ->where('course_id',$request['course_id'])
                ->first();

            if($model){
                if(empty($model->lessons_status)){
                    //get course lessons
                    $lessons_data = [];
                    $lessons = Lesson::query()->where('course_id',$model->course_id)->get();
                    foreach($lessons as $key=>$val){
                        $status = 0;
                        if($val->id == $request['lesson_id']){
                            $status = 1;
                        }
                        $lessons_data[] = ['id'=>$val->id,'status'=>$status];
                    }
                    $model->lessons_status = json_encode($lessons_data);
                    $model->save();


                }else{
                    $l = json_decode($model->lessons_status,true);
                    foreach($l as $m=>$old_lessons){
                        if($old_lessons['id'] == $request['lesson_id']){
                            $l[$m]['status'] = 1;
                            break;
                        }

                    }

                    $model->lessons_status = json_encode($l);
                    $model->save();
                }
                CoursesLearning::sendCoursePassNotification(auth()->id(),$model->course_id);

                $data = [
                    'success' => true,
                    'message' => __("messages.course_passed"),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            }else{
                return response()->json([
                    'success' => false,
                    "message" => __('messages.forbidden'),
                    "errors"=>array(),
                ], 403)->header('Status-Code', '403');
            }
        }
    }

    public function joinToCourse($id){
        $user_id = Auth::id();
        if($user_id){
            $model = Course::query()->find($id);

            if(empty($model) || $model->price > 0){
                return response()->json([
                    'success' => false,
                    "message" => __('messages.forbidden'),
                    "errors"=>array(),
                ], 403)->header('Status-Code', '403');
            }else{
                //check if already have, then skip
                $courses = CoursesLearning::query()->where('user_id', $user_id)
                    ->where('course_id',$id)
                    ->first();
               if(empty($courses)){
                   $studentCourses = New CoursesLearning();
                   $studentCourses->course_id = $id;
                   $studentCourses->user_id = $user_id;
                   $studentCourses->save();
                   $user = User::query()->find($user_id);
                   Notification::sendNotification($model->user_id, __("messages.new_member_of_course"), __("messages.course_join", ["user" => $user->first_name . " " . $user->last_name, "course" => $model->title]),"course_join", 'course', $model->id);
               }

                $data = [
                    'success' => true,
                    'message' => __("messages.added"),
                ];
                return response($data)
                    ->setStatusCode(200)->header('Status-Code', '200');
            }
        }else{
            return response()->json([
                'success' => false,
                "message" => __('messages.user_not_found'),
                "errors"=>array(),
            ], 401)->header('Status-Code', '401');
        }
    }

    public function Certificate($id){
        $user_id = Auth::id();
        $user = User::query()->where('id',$user_id)->first();
        $model = Certificates::query()->where('course_id',$id)->first();
        if($model){
            $model->student_name = $user->first_name.' '.$user->last_name;
            return response()->json(['success' => true, 'data' => $model]);
        }else{
            $data = [
                'success' => true,
                'message' => __('messages.certificate-not-found'),
            ];
            return response($data)
                ->setStatusCode(200)->header('Status-Code', '200');
        }


    }

    public function passQuize(Request $request){
        $user_id = Auth::id();
        $data = $request->all();

        if($user_id){
            $student_id = $user_id;

            $quzie_id = $data['quiz_id'];
            $questions = $data['questions'];
            $right_answer = 0;
            $total_points = 0;
            foreach($questions as $q){

                $quizeQuestion = QuizQuestion::query()->where('id',$q['id'])->first();
                $right_answers = json_decode($quizeQuestion['right_answers'],true);
                $point = 1/count($right_answers);
                $total_points += 1;

                if($q['answer'] == $right_answers){
                    $right_answer += $point;
                }

                $model = new QuizeAnswer();
                $model->student_id = $student_id;
                $model->quiz_id = $quzie_id;
                $model->question_id = $q['id'];
                $model->answer = json_encode($q['answer']);
                $model->created_at = date('Y-m-d H:i:s');
                $model->updated_at = date('Y-m-d H:i:s');
                $model->save();

            }

            $perc = round($right_answer*100/$total_points);

            //calculate correct answers
            $message = [
                'success' => true,
                'message' => 'You answerd '.$perc.'%',
            ];
            $quiz = Quiz::query()->find($quzie_id);
            $section = Section::query()->find($quiz->section_id);
            $course = Course::query()->find($section->course_id);
            Notification::sendNotification($course->user_id, __("messages.quiz_passed"), __("messages.quiz_passed_message", ["user" => auth()->user()->first_name . " " . auth()->user()->last_name, "quiz" => $quiz->title]),"pass_quiz", 'course', $course->id);
            CoursesLearning::sendCoursePassNotification(auth()->id(),$course->id);

            return response($message)
                ->setStatusCode(200)->header('Status-Code', '200');
        }else{
            return response()->json([
                'success' => false,
                "message" => "",
                "errors"=>array(),
            ], 401)->header('Status-Code', '401');
        }

    }
}
