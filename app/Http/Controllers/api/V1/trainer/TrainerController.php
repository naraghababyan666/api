<?php

namespace App\Http\Controllers\api\V1\trainer;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseResource;
use App\Models\Course;
use App\Models\CoursesLearning;
use App\Models\Payments;
use App\Models\Role;
use App\Models\Trainer;
use App\Models\Certificates;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function getUserTrainers()
    {
        if (Auth::check()) {
            if (User::isTrainerOrTrainingCenter(auth()->id())) {
                $trainers = Trainer::query()->where("user_id", auth()->id())->get();
                return response(new CourseResource($trainers))->setStatusCode(200)->header('Status-Code', '200');

            } else {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' =>__("messages.forbidden"),
                ], 403)->header('Status-Code', '403'));
            }
        } else {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => __("messages.forbidden"),
            ], 403)->header('Status-Code', '403'));
        }
    }

    public function getStatistics(){
        $courses = Course::query()->where('user_id', Auth::id())->get();
        $draft = 0;
        $under_review = 0;
        $published = 0;
        if(count($courses) != 0){
            foreach ($courses as $course){
                switch ($course['status']){
                    case Course::DRAFT:
                        $draft++;
                        break;
                    case Course::APPROVED:
                        $ids[] = $course->id;
                        $published++;
                        break;
                    case Course::UNDER_REVIEW:
                        $under_review++;
                        break;
                }
            }
        }
        $reviews = [
            1=>['name'=>1, 'count'=>0,'percent'=>'0%'],
            2=>['name'=>2, 'count'=>0,'percent'=>'0%'],
            3=>['name'=>3, 'count'=>0,'percent'=>'0%'],
            4=>['name'=>4, 'count'=>0,'percent'=>'0%'],
            5=>['name'=>5, 'count'=>0,'percent'=>'0%'],
        ];
        $total_reviews = $avg_review = $review_sum = 0;
        if(!empty($ids)){
            $id_list = implode(',',$ids);
            $sql = "select rate from reviews where course_id IN ($id_list)";
            $reviewsData = DB::select($sql);
            if(!empty($reviewsData )){
                foreach($reviewsData as $key=>$val){
                    $total_reviews ++;
                    $review_sum += $val->rate;
                    $reviews[ceil($val->rate)]['count']++;
                }
                $avg_review = number_format($review_sum / $total_reviews,2);
            }

        }
        foreach($reviews as $i=>$item){
            $reviews[$i]['percent'] = ($total_reviews>0)?number_format($item['count']*100 / $total_reviews,0).'%': '0%';

            $reviews_response[] = $reviews[$i];
        }
        $paymentsAll = Payments::query()->where('trainer_id', Auth::id())->orderBy('created_at', 'ASC')->get();
        $months = [];
        $monthly_amount = [];
        for ($i = 0; $i < count($paymentsAll); $i++){
            $monthFullName = date("F",strtotime($paymentsAll[$i]['created_at']));
            if(!in_array($monthFullName, $months)){
                $months[] = $monthFullName;
            }
        }

        $sqlPayment = "SELECT SUM(amount) as amount, `trainer_id` FROM `payments` WHERE `trainer_id` = ".Auth::id()." GROUP BY Month(created_at)  ORDER BY Month(created_at) ASC;";
        $selectFromDb = DB::select($sqlPayment);
        $priceAmount = [];
        foreach ($selectFromDb as $item){
            $priceAmount[] = $item->amount;
        }
        $g = CoursesLearning::all();
        $keys = [];
        foreach ($g as $key){
            $keys[] = $key['course_id'];
        };



        $coursesWithTrainer = Course::query()->whereIn('id',  $keys)->where('user_id', Auth::id())->select( 'title', 'trainer_id')->with(['trainer' => function($q){
            $q->select('id', 'first_name', 'last_name');
        }])->limit(3)->get()->makeHidden(['rate', 'first_lesson_date']);
        $chartsLabel = [];
        foreach ($coursesWithTrainer as $key => $item){
            $chartsLabel[$key][] = $item['title'];
            if(!is_null($item['trainer'])){
                $chartsLabel[$key][] .=  $item['trainer']['first_name'] . $item['trainer']['last_name'];
            }
        }
        $courses_paid = [
            'data' => [1500,25000, 60000],
            'courses' => $chartsLabel
        ];
        $data = [
            'total_count' => $draft+$under_review+$published,
            'draft' => $draft,
            'published' => $published,
            'under_review' => $under_review,
            'reviews'=>[
                'average_number'=>$avg_review,
                'total_reviews'=>$total_reviews,
                'rates'=>$reviews_response

            ],
            'payment_amount_monthly' => [
                'amount' => $priceAmount,
                'months' => $months
            ],
            'courses_paid' => $courses_paid
        ];
        return response()->json(['success' => true, 'data' => $data]);

    }

    public function generateCertificate(Request $request){
        $user_id = Auth::id();
        $course = Course::query()->where('id',$request['course_id'])->first();
        if($course->user_id !=$user_id){
            return response()->json([
                'success' => false,
                "message" => __('messages.forbidden'),
                "errors"=>array(),
            ], 403)->header('Status-Code', '403');
        }else{
            $model = Certificates::query()->where('course_id',$request['course_id'])->first();
            if(empty($model)){
                $model = new Certificates();
            }
            if(isset($request['headline1']) && !empty($request['headline1'])){
                $model->headline1 = $request['headline1'];
            }
            if(isset($request['headline2']) && !empty($request['headline2'])){
                $model->headline2 = $request['headline2'];
            }
            if(isset($request['headline3']) && !empty($request['headline3'])){
                $model->headline3 = $request['headline3'];
            }

            if(isset($request['description']) && !empty($request['description'])){
                $model->description = $request['description'];
            }
            if(isset($request['course_title']) && !empty($request['course_title'])){
                $model->course_title = $request['course_title'];
            }
            if(isset($request['signature']) && !empty($request['signature'])){
                $model->signature = $request['signature'];
            }

            $model->course_id = $request['course_id'];

            $model->save();
            return response()->json(['success' => true, 'data' => $model]);
        }


    }

    public function getCertificateInfo($id){
        $user_id = Auth::id();

        $course = Course::query()->where('id',$id)->first();
        if($course->user_id !=$user_id){
            return response()->json([
                'success' => false,
                "message" => __('messages.forbidden'),
                "errors"=>array(),
            ], 403)->header('Status-Code', '403');
        }else{
            $model = Certificates::query()->where('course_id',$id)->first();
            if(empty($model)){
                $model = new Certificates();
                $model->headline1 = 'Certificate';
                $model->headline2 = 'of Completion';
                $model->headline3 = 'this is to certify that';
                $model->signature = Auth::user()['first_name'] . Auth::user()['last_name'];
                $model->description = 'Has succesfully completed the';
                $model->course_title = $course->title;
                $model->course_id = $course->id;
                $model->save();
            }
            if(is_null($model['signature'])){
                $model->signature = Auth::user()['first_name'] . Auth::user()['last_name'];
                $model->save();
            }
            return response()->json(['success' => true, 'data' => $model]);
        }





    }

    public function getStatsForStats(){
        $user_id = Auth::id();
        $response = ['in_basket'=>null, 'in_wish_list'=>null];
        $sql = "select c.id, c.title, u.avatar, CONCAT(u.first_name, ' ', u.last_name) as student_name,
                 IF((c.trainer_id > 0), CONCAT(t.first_name,' ',t.last_name), CONCAT(user_trainer.first_name,' ',user_trainer.last_name)) AS trainer_name
                 from wish_lists as w
                 left join users as u on u.id=w.user_id
                 left join courses as c on w.course_id = c.id
                 left join trainers as t on t.id=c.trainer_id
                  left join users as user_trainer on user_trainer.id=c.id
          where w.course_id IN (SELECT id from courses where user_id = {$user_id} )";
        $wish_items = DB::select($sql);
        $wishInfo = null;
        if(!empty($wish_items)){
            foreach($wish_items as $wish){

                if(!isset( $wishInfo[$wish->id])){

                    $wishInfo[$wish->id] = ['id'=>$wish->id,'trainer'=>$wish->trainer_name,'title'=>$wish->title,'students'=>[]];
                }
                if(!empty($wish->avatar)){
                    $wish->avatar = env("APP_URL") . "/" . $wish->avatar;
                }
                $wishInfo[$wish->id]['students'][] = ['name'=>$wish->student_name,'avatar'=>$wish->avatar];

            }
            $wishInfo = array_values($wishInfo);

        }
        $response['in_wish_list'] = $wishInfo;


        $sql = "select c.id, c.title, u.avatar, CONCAT(u.first_name, ' ', u.last_name) as student_name,
                  IF((c.trainer_id > 0), CONCAT(t.first_name,' ',t.last_name), CONCAT(user_trainer.first_name,' ',user_trainer.last_name)) AS trainer_name
 from basket_lists as w
              left join users as u on u.id=w.user_id
              left join courses as c on w.course_id = c.id
                   left join trainers as t on t.id=c.trainer_id
                  left join users as user_trainer on user_trainer.id=c.id
          where w.course_id IN (SELECT id from courses where user_id = {$user_id} )";
        $basket_items = DB::select($sql);

        $basketInfo = null;
        if(!empty($basket_items)){
            foreach($basket_items as $basket){

                if(!isset( $basketInfo[$basket->id])){

                    $basketInfo[$basket->id] = [
                        'id'=>$basket->id,
                        'title'=>$basket->title,
                        'trainer'=>$basket->trainer_name,
                        'students'=>[]];
                }
                if(!empty($basket->avatar)){
                    $basket->avatar = env("APP_URL") . "/" . $basket->avatar;
                }
                $basketInfo[$basket->id]['students'][] =  ['name'=>$basket->student_name,'avatar'=>$basket->avatar];

            }
            $basketInfo = array_values($basketInfo);

        }
        $response['in_basket'] = $basketInfo;

        return response()->json(['success' => true, 'data' => $response]);

    }

}
