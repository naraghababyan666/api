<?php

namespace App\Http\Controllers\api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\BasketList;
use App\Models\Course;
use App\Models\Role;
use App\Models\Trainer;
use App\Models\Notification;
use App\Models\History;
use App\Models\User;
use Carbon\Doctrine\DateTimeDefaultPrecision;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class AdminController extends Controller
{
    public function trainerList(Request $request)
    {
        $page = (int) $request['page'] ?? 0;
        $limit = (int) $request['limit'] ?? 4;
        $offset = ($page - 1) * $limit;
        $sql_count = "select count(id) as total from `trainers`";
        $trainersCount = DB::select($sql_count);
        $trainerSql = "SELECT t.id, t.first_name, t.last_name, t.avatar, u.company_name FROM `trainers` as t
                            LEFT JOIN `users` as u ON t.user_id = u.id LIMIT ${offset}, ${limit} ;";

        $trainers = DB::select($trainerSql);
        foreach ($trainers as $trainer){
            $trainer->avatar = isset($trainer->avatar) ? env("APP_URL") . "/" . $trainer->avatar : null;

        }
        return response()->json(['success' => true, 'count' => $trainersCount[0]->total, 'data' => $trainers]);
    }

    public function trainerById($id){
        $trainer = Trainer::query()->select('first_name', 'last_name', 'avatar', 'bio')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $trainer]);
    }

    public function adminOrModeratorCreate(UserRequest $request)
    {
        try {
            $info = $request->all();
            if ($request['role_id'] == Role::MODERATOR || $request['role_id'] == Role::SUPER_ADMIN) {
                $newUser = User::create([
                    'first_name' => $info['first_name'],
                    'last_name' => $info['last_name'],
                    'email' => $info['email'],
                    'password' => Hash::make($info['password']),
                    'slug' => SlugService::createSlug(User::class, 'slug', $request['first_name'] . '_' . $request['last_name']),
                    'role_id' => $info['role_id'],
                ]);
                if (isset($info['avatar'])) {
                    $newUser->avatar = $info['avatar'];
                }
                $newUser->save();
                return response()->json(['success' => true, 'user' => $newUser]);
            } else {
                return response()->json(['success' => false, 'message' => __('messages.forbidden')]);
            }
        } catch (Exception $e) {

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode())->header('Status-Code', $e->getCode()));
        }
    }

    public function userList(Request $request)
    {
        $limit = (int)$request->limit;
        $roles = explode(',', $request['role_id']);
        $userQuery = User::query();
        if (isset($request->role_id)) {
            $userQuery->whereIn('role_id', $roles);
        }
        if (isset($request->search_text)) {
            $userQuery->whereRaw("LOWER(`first_name`) LIKE ? ", ['%' . trim(strtolower($request->search_text)) . '%'])
                ->orWhereRaw("LOWER(`last_name`) LIKE ? ", ['%' . trim(strtolower($request->search_text)) . '%']);
        }
        $userQuery->get();

        $filteredData = $userQuery->paginate($limit);
        foreach ($filteredData as $item){
            $item->avatar =  !is_null($item->avatar) ? env("APP_URL") . "/" . $item->avatar : null;

        }
        if (count($filteredData) == 0) {
            return response()->json(['success' => false, 'message' => __('messages.user_not_found')], 404);
        }
        return response()->json(['success' => true, 'data' => $filteredData]);
    }

    public function getUserById($id)
    {
        $user = User::query()->find($id);
        if ($user != null) {
            return response()->json(['success' => true, 'data' => $user]);
        }
        return response()->json(['success' => false, 'message' => __('messages.user_not_found')], 404);
    }

    public function deleteUser($id)
    {
        $user = User::query()->find($id);
        if ($user != null) {
            $user->delete();
            return response()->json(['success' => true, 'message' => __('messages.deleted')]);
        }
        return response()->json(['success' => false, 'message' => __('messages.user_not_found')], 404);
    }

    public function courseList(Request $request)
    {
//        $limit = (int)$request->limit;
//        $courseQuery = Course::query();
//        $types = explode(',', $request['type']);
//        $status = explode(',', $request['status']);
//
//        if (isset($request->type)) {
//            $courseQuery->whereIn('type', $types);
//        }
//        if (isset($request->status)) {
//            $courseQuery->whereIn('status', $status);
//        }
//        if (isset($request->search_text)) {
//            $courseQuery->whereRaw("LOWER(`title`) LIKE ? ", ['%' . trim(strtolower($request->search_text)) . '%'])
//                ->orWhereRaw("LOWER(`sub_title`) LIKE ? ", ['%' . trim(strtolower($request->search_text)) . '%']);
//        }
//
//        $courseQuery->get();
//        $courses = $courseQuery->paginate($limit);
//        if (count($courses) == 0) {
//            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
//        }
//        return response()->json(['success' => true, 'data' => $courses]);
//

        $where = '';
        if(isset($request->search_text)){
            $where .= "(LOWER(`title`) LIKE '%".$request->search_text."%' OR LOWER(`sub_title`) LIKE '%".$request->search_text."%')";
        }
        if(isset($request->type) && isset($request->search_text)){
            $where .= " AND `type` IN (". $request->type .")";
        }else if(isset($request->type)){
            $where .= "`type` IN (". $request->type .")";
        }
        if(isset($request->status) && (isset($request->search_text) || isset($request->type))){
            $where .= " AND `status` IN (".$request->status.")";
        }else if(isset($request->status)){
            $where .= " `status` IN (".$request->status.")";
        }
        $sql = "SELECT * FROM `courses`";
        if(!empty($request->except('page'))){
            $sql .= "WHERE ". $where;
        }
        $sql .= ' ORDER BY `updated_at` DESC';
        $for_count = DB::select($sql);
        $data_count = count($for_count);
        if($data_count == 0){
            return response()->json(['success' => false, 'message' => __('messages.course-not-found')], 404);
        }
        $limit = 10;
        $pages_count = ceil($data_count / $limit);
        if($pages_count < $request->page){
            return response()->json(['success' => false, 'message' => __('messages.invalid-page')]);
        }
        $current_page = $request->page ?? 1;
        $skip = ($current_page-1)*$limit;

        $from = $limit * ($current_page -1) + 1;
        $to = $limit * ($current_page);

        $sql .= " LIMIT {$skip}, $limit";
        $full_data = DB::select($sql);

        if($to > $data_count){
            $to = $data_count;
        }
        if($from > $data_count){
            $from = $data_count;
        }
        foreach ($full_data as $item){
            $item->cover_image =  !is_null($item->cover_image) ? env("APP_URL") . "/" . $item->cover_image : null;
        }
        $data = [
            'total' => $data_count,
            'per_page' => $limit,
            'from' => $from,
            'to' => $to,
            'current_page' => $current_page,
            'data' => $full_data
        ];
        return response()->json(['success' => true, 'data' => $data]);


    }

    public function getCourseById($id)
    {
        $course = Course::query()->find($id);
        if ($course != null) {
            return response()->json(['success' => true, 'data' => $course]);
        }
        return response()->json(['success' => false, 'message' => __('messages.course-not-found')], 404);
    }

    public function deleteCourse($id)
    {
        $course = Course::query()->find($id);
        if ($course != null) {
            $course->status = Course::DELETED;
            $course->save();
            return response()->json(['success' => true, 'message' => __('messages.deleted')]);
        }
        return response()->json(['success' => false, 'message' => __('messages.course-not-found')], 404);
    }

    public function changeCourseStatus($id, Request $request)
    {
        $status_code = $request->status_code;
        $course = Course::query()->find($id);
        if (in_array($status_code, [1, 2, 3, 4, 5])) {
            if ($course != null) {
                $course->status = (int)$status_code;
                $course->save();
                return response()->json(['success' => true, 'message' => __('messages.course-changed-status'), 'course' => $course]);
            }
            return response()->json(['success' => false, 'message' => __('messages.course-not-found')], 404);
        }
        return response()->json(['success' => false, 'message' => __('messages.course-invalid-status')]);

    }

    public function updateStatus($id, Request $request){
        $model = Course::query()->find($id);
        $data = $request->all();
        $this->saveCourseHistory($id);
        if($data['status'] == Course::APPROVED){
            $type="approved";
            $message =  "Congrats your course is approved";
            $model->status = Course::APPROVED;
            $model->save();

        }elseif($data['status'] == Course::DECLINED){
            $type="declined";
            $message = "Your course is declined by moderator";
            $model->status = Course::DECLINED;
            $model->declined_reason = $data['declined_reason'];
            $model->save();
        }

        $a = Notification::create([
            "user_id" => $model->user_id,
            "title" => $message,
            "message" => $message,
            "status" => 0,
            "type" => $type??"info",
            'item'=> 'course',
            'item_id' => $model->id,
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
         return response()->json(['success' => true, 'message' => __('messages.course-updated-status')]);

    }

    private function saveCourseHistory($id)
    {

        $model = Course::query()->with(["lessons", "trainer"])->find($id);
        if($model->type == Course::ONLINE){

            $with = ["sections","sections.lessons","sections.quiz","trainer"];
            $model = Course::query()->with($with)->find($id);
        }
        $history = History::query()->where("course_id", $id)->where("user_id", $model->user_id)->first();
        if ($history) {
            $history->course_id = $id;
            $history->user_id=$model->user_id;
            $history->old_value=json_encode($model);
            $history->save();
        } else {
            History::create([
                "course_id" => $id,
                "user_id" => $model->user_id,
                "old_value" => json_encode($model)
            ]);
        }
    }

    public function getCourseDetails($id){
        $model = Course::query()->with(["lessons", "trainer"])->find($id);

        if($model->type == Course::ONLINE){

            $with = ["sections","sections.lessons","sections.quiz","trainer"];
            $model = Course::query()->with($with)->find($id);
        }
        $history = History::query()->where("course_id", $id)->where("user_id", $model->user_id)->first();
        $fields = Course::detailViewForAdmin($model->type);
        //echo"<pre>";print_r($fields);die;
        $new_value = [];
        foreach($fields as $key=>$val){

                $value = null;
                if(isset($model->$val)){
                    if($val == 'requirements' || $val == 'will_learn' ){
                        if(!empty($model->$val)){
                            $value = json_decode($model->$val,true);
                        }else{
                            $value = null;
                        }

                    }elseif($val == 'level'){
                        $value = Course::getLevelName($model->level);
                    }elseif($val == 'language'){
                        $value = Course::getLanguageName($model->language);
                    }else{
                        $value = $model->$val;
                    }
                }else{
                    if($val == 'trainer'){

                        if($model->trainer){

                            $value = $model->trainer->first_name.' '. $model->trainer->last_name;
                        }else{
                            $value = $model->user->first_name.' '. $model->user->last_name;

                        }
                    }
                    if($val == 'user_name'){
                       $value = $model->user->first_name.' '.$model->user->last_name;
                    }
                }
                $new_value[$val] = $value;

        }
        $changes = [];
        $old_value_fields= [];
        if(!empty($history)){
           $old_value = json_decode($history->old_value);
            $user = User::query()->where('id',$old_value->user_id)->first();

            foreach($fields as $key=>$val){
                if($val == 'trainer'){
                    if($val == 'user_name'){
                        $old_value_fields[$val] =  $user->first_name.' '.$user->last_name;
                        continue;
                    }
                    if($model->trainer){

                        $value = $model->trainer->first_name.' '. $model->trainer->last_name;
                    }else{
                        $value =  $user->first_name.' '.$user->last_name;

                    }

                    $old_value_fields[$val] =  $value;
                    continue;
                }


                if(property_exists($old_value,$val)){
                    if($val == 'requirements' || $val == 'will_learn' ){
                        if(!empty($old_value->$val)){
                            $value = json_decode($old_value->$val,true);
                        }else{
                            $value = null;
                        }

                    }elseif($val == 'level'){
                        $value = Course::getLevelName($old_value->level);
                    }elseif($val == 'language'){
                        $value = Course::getLanguageName($old_value->language);
                    }else{
                        $value = $old_value->$val;
                    }

                    $old_value_fields[$val] =  $value;
                }
               // var_dump($val);
            }

        }
        $data = ['new_value'=>$new_value,'old_value'=>$old_value_fields];
        return response()->json(['success' => true, 'message' => __('messages.course-updated-status'),'data'=>$data]);

    }

    public function coursesInBasketList(){
        $sql = "SELECT b.course_id, c.title, (SELECT CONCAT(first_name, ' ', last_name) from `users` where id = c.user_id) as course_creator, JSON_ARRAYAGG(JSON_OBJECT(
                'user_id', users.id,
                'avatar', users.avatar,
                'user_name', (select CONCAT(first_name, ' ', last_name) from `users` WHERE id = b.user_id)
            )) as user_list
            FROM `basket_lists` as b
            inner join `courses` as c ON c.id = b.course_id
            inner join `users` on users.id = b.user_id
            GROUP BY course_id";

        $data = DB::select($sql);
        if(count($data) != 0){
            foreach ($data as $item){
                $item->user_list = json_decode($item->user_list);
                if(count($item->user_list) > 0){
                    foreach ($item->user_list as $a){
                        $a->avatar = !is_null($a->avatar) ? env("APP_URL") . "/" . $a->avatar : null;
                    }
                }
            }
            return response()->json(['success' => true, 'data' => $data]);
        }
        return response()->json(['success' => true, 'message' => 'Empty list']);

    }

    public function coursesInWishList(){
        $sql = "SELECT b.course_id, c.title, (SELECT CONCAT(first_name, ' ', last_name) from `users` where id = c.user_id) as course_creator, JSON_ARRAYAGG(JSON_OBJECT(
                'user_id', users.id,
                'avatar', users.avatar,
                'user_name', (select CONCAT(first_name, ' ', last_name) from `users` WHERE id = b.user_id)
            )) as user_list
            FROM `wish_lists` as b
            inner join `courses` as c ON c.id = b.course_id
            inner join `users` on users.id = b.user_id
            GROUP BY course_id";
        $data = DB::select($sql);
        if(count($data) != 0){
            foreach ($data as $item){
                $item->user_list = json_decode($item->user_list);
                foreach ($item->user_list as $a){
                    $a->avatar = !is_null($a->avatar) ? env("APP_URL") . "/" . $a->avatar : null;
                }
            }
            return response()->json(['success' => true, 'data' => $data]);
        }
        return response()->json(['success' => true, 'message' => 'Empty list']);
    }
}
