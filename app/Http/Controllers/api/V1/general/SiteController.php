<?php

namespace App\Http\Controllers\api\V1\general;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Language;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Exception;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;
use \Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public  $filter = [];

     public function getLanguages(){
         $languages = Language::query()->get();
         foreach ($languages as $lang){
             $lang->title = __("messages.". $lang->title);
         }
         return  response($languages)->setStatusCode(200)->header('Status-Code', '200');
     }
     public function getRoles(){
         $roles = Role::query()->get();
         return  response($roles)->setStatusCode(200)->header('Status-Code', '200');
     }
     public function getCourseStatuses(){
         $data = Course::getStatus(0, true);
         return  response($data)->setStatusCode(200)->header('Status-Code', '200');
     }
     public function getCourseTypes(){
         $data = Course::getType(0, true);
         return  response($data)->setStatusCode(200)->header('Status-Code', '200');
     }
     public function getCourseLevels(){
         $data = Course::getLevels(0,  true);
         return  response($data)->setStatusCode(200)->header('Status-Code', '200');
     }

     public function homePageStatistics(){
         $coursesCount = Course::query()->where('status', Course::APPROVED)->count() ;
         $users = User::query()->whereIn('role_id', array(3, 4, 5))->get() ;
         $studentsCount = $trainersCount = 0;
         foreach($users as $u){
             if($u->role_id == 5){
                 $studentsCount +=1;
             }else{
                 $trainersCount +=1;
             }
         }
         return response()->json(['success' => true, 'data' => ['courses' => $coursesCount, 'students' => $studentsCount, 'trainers' => $trainersCount]]);
     }

     public function autocompleteText($text){
         $text = strtolower($text);

        $coursesListForFilterTextSql = "SELECT * FROM `courses`
                                            WHERE (LOWER(`title`) LIKE '%".trim(strtolower($text))."%' OR LOWER(`sub_title`) LIKE '%".trim(strtolower($text))."%')
                                            AND status = ".Course::APPROVED;
         $coursesListForFilterText = DB::select($coursesListForFilterTextSql);

     $categoriesSql = "SELECT * FROM `category_translations` WHERE LOWER(`title`) LIKE '%" . trim(strtolower($text)) . "%'" ;
         $categories = DB::select($categoriesSql);

         $coursesListForReturnSql = "SELECT c.id,c.cover_image,c.status,c.title, c.user_id, c.trainer_id,
                                        CONCAT(trainers.first_name,' ',trainers.last_name) AS trainer_name
                                        FROM `courses` as c
                                         LEFT JOIN `trainers` ON c.trainer_id = trainers.id
                                         WHERE `status` = ".Course::APPROVED."
                                         AND LOWER(`title`) LIKE '%" . trim(strtolower($text)) . "%'
                                         ORDER BY RAND() LIMIT 4 ";
         $courseListForReturn = DB::select($coursesListForReturnSql);

         foreach ($courseListForReturn as $item){
             if($item->trainer_id != null){
                 $trainer = Trainer::where('id', $item->trainer_id)->first();
                 $item->trainer_name = $trainer['first_name'] .' '. $trainer['last_name'];
             }else{
                 $user = User::where('id', $item->user_id)->first();

                 $item->trainer_id = $user['id'];
                 $item->trainer_name = $user['first_name'].' '.$user['last_name'];
             }

             $item->cover_image = isset($item->cover_image)?env("APP_URL")."/".$item->cover_image:null;
         }

         if($coursesListForFilterText || $categories){
             $this->loop($coursesListForFilterText, 'title', $text);
             $this->loop($coursesListForFilterText, 'sub_title', $text);
             $this->loop($categories, 'title', $text);
             $this->filter = array_unique($this->filter);
             if(count($courseListForReturn) + count($this->filter) >= 10){
                 if(count($this->filter) > 10){
                     $this->filter = array_slice($this->filter, 0, 10);
                 }
             }
             $texts = [];
             foreach ($this->filter as $text){
                 $texts[] = $text;
             }
             return response()->json([
                 'success' => true,
                 'text' => $texts,
                 'courses' => $courseListForReturn
             ], 200);

         }
         return response()->json(['fail' => __('messages.no-filtered-text')], 204);

     }

     function loop($object, $column, $text){
         foreach ($object as $item) {
             if (str_contains(strtolower($item->$column), $text)) {
                 array_push($this->filter, $item->$column);
             }
         }
     }

    public function searchFilter(Request $request)
    {
        $data = $request->all();
        $language = $this->language_id;
        $where_text = $find_lists =  '';
        $find = [];
        $token = $request->bearerToken();

        if (!empty($token)) {
            $user = auth('sanctum')->user();
            $user_id = $user->getAuthIdentifier();
            if (!empty($user_id)) {
                $find[] = " (select count(*) from wish_lists as w where w.user_id={$user_id} and c.id=w.course_id) as in_wishlist ";
                $find[] = " (select count(*) from basket_lists as b where b.user_id={$user_id} and c.id=b.course_id) as in_basket ";
                $find_lists = implode(',', $find) . ',';
            }
        }
        $where[] = 'c.status = ?';

        $current_page = isset($data['current_page'])?$data['current_page']:1;
        $limit = (isset($data['limit']))?$data['limit']:10;
        $skip =  ($current_page-1)*$limit;
//highest-rated
        //newest
        $sort = "ORDER BY c.updated_at DESC";
        if( !empty($data['sort'])){
            if($data['sort'] == 'highest-rated'){
                $sort = "ORDER BY rating DESC";
            }elseif($data['sort'] == 'newest'){
                $sort = "ORDER BY c.updated_at DESC";
            }

        }
        if( !empty($data['categories'])){
            $where[] = "c.category_id IN (".$data['categories'].")";
        }
        if( !empty($data['type'])){
            $where[] = "c.type IN (".$data['type'].")";
        }

        if( !empty($data['level'])){
            $data['level'] = $data['level'].', '.Course::ALL_LEVELS;
            $where[] = "c.level IN (".$data['level'].")";
        }
        if( !empty($data['language_id'])){
            $where[] = "c.language IN (".$data['language_id'].")";
        }
        if( !empty($data['search_text'])){
            $data['search_text'] = addslashes($data['search_text']);
            $where[] = "(c.title LIKE '%".$data['search_text']."%' OR c.sub_title LIKE '%".$data['search_text']."%') ";
        }
        if(isset($data['price_from']) && isset($data['price_to'])){
            $where[] = "( c.price BETWEEN {$data['price_from']} AND {$data['price_to']})";
        }
        if(!empty($where)){
            $where_text = implode(' AND ', $where);
        }

        $sql = "SELECT {$find_lists} c.type, parent_tr.title as parent_category_title, c.category_id, c.cover_image, c.id, c.sub_title, c.title, cat.title as category_title, c.currency, c.price,c.created_at,
                IF((c.trainer_id > 0), CONCAT(t.first_name,' ',t.last_name), CONCAT(u.first_name,' ',u.last_name)) AS trainer_name,
                IF((c.trainer_id > 0), t.avatar, u.avatar) AS trainer_avatar,
                (select avg(rate) from reviews as r where r.course_id=c.id) as rating,
                c.user_id as profile_id,
                IF((c.type = 2 OR c.type=3), (SELECT ll.start_time from lessons as ll where ll.course_id = c.id order by ll.start_time asc LIMIT 1 ) , null) AS first_lesson_date,
                IF((c.type = 2 OR c.type=3), (SELECT ll.start_time from lessons as ll where ll.course_id = c.id order by ll.start_time desc LIMIT 1 ) , null) AS last_lesson_date

                from courses as c
                LEFT join users as u on c.user_id=u.id
                LEFT join categories as category ON category.id = c.category_id
                LEFT join category_translations as cat on (category.id=cat.category_id AND cat.language_id = ?)
                LEFT join trainers as t on c.trainer_id=t.id
                LEFT join categories as parent_c on parent_c.id=category.parent_id
                LEFT join category_translations  as parent_tr on (parent_tr.category_id=category.parent_id AND parent_tr.language_id = {$language})
                where {$where_text}
                  ";

        $sql_count = "select count(id) as total from ($sql) as page_count";
        $sql_updated = $sql.'  '.$sort;
//var_dump($sql_updated);die;
        $sql_updated = $sql_updated.' '." LIMIT {$skip}, $limit";


        $result_count = DB::select($sql_count,[$language,Course::APPROVED]);

        $result = DB::select($sql_updated,[$language,Course::APPROVED]);
        $resultArray = json_decode(json_encode($result), true);
        foreach($resultArray as $key => $val){
            if(!isset($val['in_wishlist'])){
                $resultArray[$key]['in_wishlist'] = 0;
                $resultArray[$key]['in_basket'] = 0;
            }
        }
        $filteredArray = [];
        foreach ($resultArray as $item) {
            if(($item['type'] == 2 || $item['type'] == 3) && Carbon::now() > $item['last_lesson_date']){
                unset($item);
            }else {
                $item['cover_image'] = isset($item['cover_image']) ? env("APP_URL") . "/" . $item['cover_image'] : null;
                if (!empty($item['parent_category_title'])) {
                    $item['categories'][] = $item['parent_category_title'];
                }
                $item['categories'][] = $item['category_title'];
                if (!empty($item['trainer_avatar'])) {
                    $item['trainer_avatar'] = env("APP_URL") . "/" . $item['trainer_avatar'];
                }
                $filteredArray[] = $item;
            }
        }

        return response()->json(['data' => $filteredArray, 'total_count'=>$result_count[0]->total,'per_page'=>$limit,'current_page'=>$current_page], 200);
    }
     public function fileUpload(Request $request){

         if (isset($request["file"])) {
             $file = $request["file"];
             if ($request['type'] == 'resource') {
                 $validator = Validator::make($request->all(), [
                     'file' => 'mimes:pdf,doc,docx,xlsx,pptx,xml,odt|max:5000'
                 ]);
             } else {
                 $validator = Validator::make($request->all(), [
                     'file' => 'mimes:jpeg,png,jpg,gif,svg,mp4,webm,mov,wmv,avi|max:5000'
                 ]);
             }
             if ($validator->fails()) {
                 return response()->json([
                     'success' => false,
                     "errors" => $validator->errors()
                 ])->header('Status-Code', 200);
             }
             $loc = $request["type"];
             $location = $loc ? $loc . "/" : "";
//             try {
                 $dir = "files";
                 $mime = explode("/", $file->getClientMimeType());
                 if ($mime[0] == "image") {
                     $dir = "images";
                 } elseif ($mime[0] == "video") {
                     $dir = "videos";
                 }
                 $dirPath = $dir . '/' . $location;
                 $filename = $dirPath . date('mdYHis') . "_" . $loc . '.' . $file->getClientOriginalExtension();
                 if ($dir == 'images' && $request["type"] == "user") {
                     File::ensureDirectoryExists(public_path('images/user'));
                     $img = Image::make($file->getRealPath());
                     $img->resize(200, 200, function ($const) {
                         $const->aspectRatio();
                     })->save(public_path($filename));
                 } else if ($dir == 'images' && $request["type"] == "course") {
                     File::ensureDirectoryExists(public_path('images/course'));
                     $img = Image::make($file->getRealPath());
                     $img->resize(400, 250, function ($const) {
                         $const->aspectRatio();
                     })->save(public_path($filename));
                 } else {
                     $file->move(public_path($dirPath), $filename);
                 }
                 if ($request["type"] == "user" && !empty(auth()->id())) {
                     $user = User::query()->find(auth()->id());
                     File::delete(public_path($user->avatar));
                     $user->avatar = $filename;
                     $user->save();
                 }
                 $url = env("APP_URL") . "/" . $filename;
                 return response()->json([
                     'success' => true,
                     "data" => [
                         'path' => $filename,
                         'url' => $url
                     ]
                 ], 200);
//             } catch (Exception $e) {
//                 return response()->json([
//                     'success' => false,
//                     "errors" => $validator->errors()
//                 ])->header('Status-Code', 200);
//             }

         }
     }


    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(),
            ['email' => ['required', 'string', 'email', 'max:255', 'unique:subscriptions']]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ], 200)->header('Status-Code', '200');
        }
        $subscriptions = Subscription::create(
            [
                "email" => $request->email
            ]
        );

        return response()->json([
            'success' => true,
            "message" => __("messages.subscribe")
        ], 200)->header('Status-Code', '200');
    }
}
