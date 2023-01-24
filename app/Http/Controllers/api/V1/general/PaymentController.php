<?php

namespace App\Http\Controllers\api\V1\general;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CoursesLearning;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function studentsList(){

        $getMyCourses = CoursesLearning::query()
            ->leftJoin('courses', 'course_id', '=', 'courses.id')
            ->where('courses.user_id', '=', Auth::id())
            ->select('courses_learning.id', 'courses_learning.course_id','courses_learning.user_id')
            ->with(['buyerData' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'avatar');
            }])
            ->get();
        if(count($getMyCourses) != 0){

            foreach ($getMyCourses as $k=>$item){

                $item['user_first_name'] = $item['buyerData']['first_name'];
                $item['user_last__name'] = $item['buyerData']['last_name'];
                $item['user_avatar'] = !is_null($item['buyerData']['avatar']) ? env("APP_URL") . "/" . $item['buyerData']['avatar'] : null;
                unset($item['buyerData']);
                $item['participated_course'] = 1;
                foreach($getMyCourses as $e){
                    if($e['user_id'] == $item['user_id'] && $e['id'] !=$item['id']){
                        $item['participated_course']+=1;
                    }
                }
            }
            $existing = [];
            foreach($getMyCourses as $k=> $i){
                if(in_array($i['user_id'],$existing)){
                    unset($getMyCourses[$k]);
                }
                    $existing[]  = $i['user_id'];
               }
            return response()->json(['success' => true, 'data' => $getMyCourses]);
        }
        return response()->json(['success' => true, 'message' => __('messages.course-not-found')]);
    }


        public function studentCourses($id,Request $request)
        {
            $data = $request->all();
            $language = $this->language_id;
            $where_text = $find_lists =  '';
            $find = [];
            $token = $request->bearerToken();

            if (!empty($token)) {
                $user = auth('sanctum')->user();
                $user_id = $user->getAuthIdentifier();

            }
            $student_id = $id;
            $where[] = 'c.status = ?';

            $current_page = isset($data['current_page'])?$data['current_page']:1;
            $limit = (isset($data['limit']))?$data['limit']:10;
            $skip =  ($current_page-1)*$limit;

            $sort = "ORDER BY c.updated_at DESC";

            $sql = "SELECT {$find_lists} c.type,cl.lessons_status, parent_tr.title as parent_category_title, c.category_id, c.cover_image, c.id, c.sub_title, c.title, cat.title as category_title, c.currency, c.price,c.created_at,
                IF((c.trainer_id > 0), CONCAT(t.first_name,' ',t.last_name), CONCAT(u.first_name,' ',u.last_name)) AS trainer_name,
                IF((c.trainer_id > 0), t.avatar, u.avatar) AS trainer_avatar,
                (select avg(rate) from reviews as r where r.course_id=c.id) as rating,
                c.user_id as profile_id,
                IF((c.type = 2 OR c.type=3), (SELECT ll.start_time from lessons as ll where ll.course_id = c.id order by ll.start_time asc LIMIT 1 ) , null) AS first_lesson_date

                from courses as c
                LEFT join users as u on c.user_id=u.id
                LEFT join courses_learning as cl on  (cl.course_id = c.id and cl.user_id={$student_id})
                LEFT join categories as category ON category.id = c.category_id
                LEFT join category_translations as cat on (category.id=cat.category_id AND cat.language_id = ?)
                LEFT join trainers as t on c.trainer_id=t.id
                LEFT join categories as parent_c on parent_c.id=category.parent_id
                LEFT join category_translations  as parent_tr on (parent_tr.category_id=category.parent_id AND parent_tr.language_id = {$language})
                where c.user_id={$user_id} and cl.user_id = {$student_id}
                  ";


            $sql_count = "select count(id) as total from ($sql) as page_count";
            $sql_updated = $sql.'  '.$sort;
//var_dump($sql_updated);die;
            $sql_updated = $sql_updated.' '." LIMIT {$skip}, $limit";


            $result_count = DB::select($sql_count,[$language]);

            $result = DB::select($sql_updated,[$language]);
            $resultArray = json_decode(json_encode($result), true);
            foreach($resultArray as $key => $val){
                if(!isset($val['in_wishlist'])){
                    $resultArray[$key]['in_wishlist'] = 0;
                    $resultArray[$key]['in_basket'] = 0;
                }
            }
            $filteredArray = [];
            foreach ($resultArray as $item) {

                $item['cover_image'] = isset($item['cover_image']) ? env("APP_URL") . "/" . $item['cover_image'] : null;
                if(!empty($item['parent_category_title'])){
                    $item['categories'][]=$item['parent_category_title'];
                }
                $item['categories'][]=$item['category_title'];
                if(!empty($item['trainer_avatar'])){
                    $item['trainer_avatar'] = env("APP_URL") . "/" . $item['trainer_avatar'];
                }
                if($item['type'] == Course::ONLINE){
                    //need calcluate for now just send standart text
                   if(!empty($item['lessons_status'])){
                       $objects = json_decode($item['lessons_status'],true);
                       $total_count = count($objects);
                       $sum = 0;
                       foreach($objects as $obj){
                           $sum +=$obj['status'];
                       }
                       $perc = round($sum*100/$total_count);
                       $item['lessons_status'] = $perc.'%';

                   }else{
                       $item['lessons_status'] ='0%';
                   }

                }else{
                    $item['lessons_status'] = '';
                }
                $filteredArray[] = $item;
            }

            return response()->json(['data' => $filteredArray, 'total_count'=>$result_count[0]->total,'per_page'=>$limit,'current_page'=>$current_page], 200);
        }

}
