<?php

namespace App\Models;

use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Course extends Model
{
    use HasFactory, Sluggable;

    public $completed_steps;
    public $creation_percent;


    protected $fillable = [
        'user_id',
        'cover_image',
        'promo_video',
        'title',
        'description',
        'sub_title',
        'language',
        'type',
        'status',
        'category_id',
        'max_participants',
        'level',
        'definition',
        'trainer_id',
        'price',
        'currency',
        'updated_at',
        'created_at',
        'slug',
        "requirements",
        "will_learn",
        "lessons_count",
        "certificate",
        "declined_reason"
    ];
    public const FIRST_STEP=["category_id"];
    public const SECOND_STEP = [
        'title',
        'description',
        'sub_title',
        'cover_image',
        ];
    public const THIRD_STEP = [
        'language',
        'max_participants',
        'level',
        'currency',
        "lessons_count",
        'address',
        "lessons"
        ];
    public const FORTH_STEP = [
        'trainer_id',
    ];


    protected $appends = ["rate","first_lesson_date", "last_lesson_date"];
    protected $hidden = ["rates"];

// Course statuses
    public const DRAFT = 1;
    public const UNDER_REVIEW = 2;
    public const APPROVED = 3;
    public const DECLINED = 4;
    public const DELETED = 5;

// Course types
    public const ONLINE = 1;
    public const OFFLINE = 2;
    public const ONLINE_WEBINAR = 3;
    public const CONSULTATION = 4;

// Course levels
    public const ALL_LEVELS = 1;
    public const BEGINNER = 2;
    public const MIDDLE = 3;
    public const ADVANCED = 4;


    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id', 'id');
    }

    public function wishList()
    {
        return $this->hasMany(WishList::class, 'course_id', 'id');
    }

    public function trainer()
    {
        return $this->hasOne(Trainer::class, 'id', 'trainer_id');
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function sections()
    {
        return $this->hasMany(Section::class, 'course_id', 'id');
    }

    public function courseslearning(){
        return $this->hasMany(CoursesLearning::class, 'id', 'course_id');
    }


    public static function getStatus($id = 0, $isAll = false)
    {
        $statuses = [
            self::DRAFT => __('messages.status-Draft'),
            self::UNDER_REVIEW =>__('messages.status-Under review'),
            self::APPROVED => __('messages.status-Approved'),
            self::DECLINED =>__('messages.status-Declined'),
            self::DELETED =>__('messages.status-Deleted status'),
        ];
        if ($isAll) {
           return array_flip($statuses);
        }
        return $statuses[$id];
    }

    public static function getType($id = 0, $isAll = false)
    {
        $types = [
            self::ONLINE => __('messages.type-online'),
            self::OFFLINE =>__('messages.type-offline'),
            self::ONLINE_WEBINAR => __('messages.type-online-webinar'),
            self::CONSULTATION => __('messages.type-consultation'),
        ];
        if ($isAll) {
            return array_flip($types);
        }
        return $types[$id];
    }

    public static function getLevels($id = 0, $isAll = false)
    {
        $levels = [
            self::ALL_LEVELS =>  __('messages.level-All levels'),
            self::BEGINNER =>  __('messages.level-Beginners'),
            self::MIDDLE =>  __('messages.level-Middle level'),
            self::ADVANCED =>  __('messages.level-Advanced'),
        ];
        if ($isAll) {
            return array_flip($levels);
        }
        return $levels[$id];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function rates(){
        return $this->hasMany(Review::class)->orderBy('updated_at', 'DESC');
    }
    public function getRateAttribute()
    {
        return   Review::query()->where("course_id",$this->id)->average("rate");
    }
    public function getFirstLessonDateAttribute()
    {
        if(!empty($this->attributes['id'])){
            $lesson = Lesson::query()->where("course_id",$this->attributes['id'])->whereNotNull(['start_time'])->orderBy("start_time")->first();
            if(is_null($lesson)){

                return null;
            }
            return $lesson->start_time;
        }

    }
    public function getLastLessonDateAttribute()
    {
        if(!empty($this->attributes['id'])){
            $lesson = Lesson::query()->where("course_id",$this->attributes['id'])->whereNotNull(['start_time'])->orderBy("start_time", 'desc')->first();
            if(is_null($lesson)){
                return null;
            }
            return $lesson->start_time;
        }

    }
    public function getCoverImageAttribute()
    {

        return   isset($this->attributes['cover_image']) ? env("APP_URL") . "/" . $this->attributes['cover_image'] : null;
    }

    public function basket(){
        return $this->hasMany(BasketList::class);
    }
    public static function getNamesArray($data)
    {
        $names = [];

        foreach (self::recursiveFind($data, "title") as $value) {
            $names [] = $value;
        }
        return $names;
    }
    public static function getChildrenArray($data)
    {
        $names = [];
        foreach (self::recursiveFind($data, "id") as $value) {
            $names [] = $value;
        }
        return $names;
    }

    public static function recursiveFind(array $haystack, $needle)
    {
        $iterator = new \RecursiveArrayIterator($haystack);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                yield $value;
            }
        }
    }

    public static function getStepsArray()
    {
        return [
            "first" => self::FIRST_STEP,
            "second" => self::SECOND_STEP,
            "third" => self::THIRD_STEP,
            "forth" => self::FORTH_STEP,
        ];
    }

    public static function getCompletedSteps($course, $percent = false)
    {
        $completedSteps = [];
        if($course->user->role_id == Role::TRAINING_CENTER){
            $allProps =   array_merge( self::FIRST_STEP, self::SECOND_STEP, self::THIRD_STEP, self::FORTH_STEP);
        }else{
            $allProps =   array_merge( self::FIRST_STEP, self::SECOND_STEP, self::THIRD_STEP);
        }
        $allPropsCount =  count($allProps);
        $steps = self::getStepsArray();
        if($course->user->role_id == Role::TRAINER){
            unset($steps["forth"]);
        }
        foreach ($steps as $key => $step) {
            $completed = true;
            foreach ($step as $item) {
                if ($course->type == Course::ONLINE_WEBINAR && $item == "address") {
                    if (empty($course->link)) {
                        $completed = false;
                        $allPropsCount --;
                    }
                }elseif ($course->type == Course::ONLINE && ($item == "lessons_count" || $item == "address")){
                    continue;
                }
                elseif ($course->type == Course::ONLINE && $item == "lessons") {
                    if(isset($course["sections"][0])){
                        if (count($course["sections"][0]["lessons"])==0) {
                            $completed = false;
                            $allPropsCount --;
                        }
                    }
                } else {
                    if (empty($course->$item)) {
                        $completed = false;
                        $allPropsCount --;
                    }
                }
            }
            $completedSteps[$key] = $completed;
        }

        if ($percent) {
            return ($allPropsCount * 100) / count($allProps);
        }
        return $completedSteps;
    }

    public static function getExpiredCourses($data){
        if(in_array($data['type'], [Course::OFFLINE, Course::ONLINE_WEBINAR])){
            if($data->last_lesson_date > Carbon::now()){
                return $data;
            }
            return null;
        }
        return $data;
    }

    public static function getListOfCourses($data){
        $language = $data["language_id"];
        $where_text = $find_lists =  '';
        $find = [];
        $token =   $data['token']?? null;
        if (!empty($token)) {
            $user = auth('sanctum')->user();
            $user_id = $user->getAuthIdentifier();
        }else{
            $user_id = (isset($data['user_id']) && ($data['user_id'] > 0 )) ? $data['user_id']: null;

        }
        if(!empty($user_id)){
            $find[] = " (select count(*) from wish_lists as w where w.user_id={$user_id} and c.id=w.course_id) as in_wishlist ";
            $find[] = " (select count(*) from basket_lists as b where b.user_id={$user_id} and c.id=b.course_id) as in_basket ";
            $find_lists = implode(',',$find).',';
        }
        $where[] = 'c.status = ?';
        $limit = (isset($data['limit']))?$data['limit']:10;
        $page =  (isset($data['page']))?$data['page']:1;
        $skip =  ($page-1)*$limit;

        if(isset($data['categories']) && !empty($data['categories'])){
            $where[] = "c.category_id IN (".$data['categories'].")";
        }
        if(isset($data['type']) && !empty($data['type'])){
            $where[] = "c.type IN (".$data['type'].")";
        }
        if(isset($data['level']) && !empty($data['level'])){
            $data['level'] = $data['level'].', '.Course::ALL_LEVELS;
            $where[] = "c.level IN (".$data['level'].")";
        }
        if(isset($data['language_id']) && !empty($data['language_ids'])){
            $where[] = "c.language IN (".$data['language_ids'].")";
        }
        if(isset($data['search_text']) && !empty($data['search_text'])){
            $where[] = "(c.title LIKE '%".$data['search_text']."%' OR c.sub_title LIKE '%".$data['search_text']."%') ";
        }
        if(isset($data['currency']) && !empty($data['currency'])){
            $where[] = "(c.currency = '{$data['currency']}' AND c.price BETWEEN {$data['price_from']} AND {$data['price_to']})";
        }
        if(isset($data['course_ids']) && !empty($data['course_ids'])){
            $where[] = "c.id  IN (".$data['course_ids'].")";
        }
        if(!empty($where)){
            $where_text = implode(' AND ', $where);
        }
        $time = Carbon::now();
        $sql = "SELECT {$find_lists} c.type, parent_tr.title as parent_category_title, c.category_id, c.cover_image, c.id, c.sub_title, c.title, cat.title as category_title, c.currency, c.price,c.created_at,c.discount_percent,
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

        if($limit != -1){
            $sql_updated = $sql.' '." LIMIT {$skip}, $limit";
        }else{
            $sql_updated = $sql;
        }
        $result_count = DB::select($sql_count,[$language, Course::APPROVED]);

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
            if($item['type'] == Course::OFFLINE || $item['type'] == Course::ONLINE_WEBINAR){
                if($item['last_lesson_date'] < Carbon::now()){
                    continue;
                }
            }
            $item['cover_image'] = isset($item['cover_image']) ? env("APP_URL") . "/" . $item['cover_image'] : null;
            if(!empty($item['parent_category_title'])){
                $item['categories'][]=$item['parent_category_title'];
            }
            $item['categories'][]=$item['category_title'];
            if(!empty($item['trainer_avatar'])){
                $item['trainer_avatar'] = env("APP_URL") . "/" . $item['trainer_avatar'];
            }
            $filteredArray[] = $item;
        }
        return array('data'=>$filteredArray,'total_count'=>$result_count[0]->total);

    }

    public static function detailViewForAdmin($type){
        $fields = [
            'id',
            'user_id',
            'user_name',
            'cover_image',
            'promo_video',
            'title',
            'description',
            'language',
            'status',
            'category_id',
            'level',
            'certificate',
            'trainer',
            'price',
            'will_learn',
            'requirements',
            //'lessons'=>[['duration','start_time','title']],

        ];
        if($type == self::ONLINE_WEBINAR){
            $added_field = [
                'lessons_count',
                'max_participants',
                'link',
                //'lessons'=>[['duration','start_time','title']],
            ];
            $fields = array_merge($fields,$added_field);
        }elseif($type == self::ONLINE){
            $added_field = [
                'sections',
            ];
            $fields = array_merge($fields,$added_field);
        }elseif($type == self::OFFLINE){
            $added_field = [
                'lessons_count',
                'max_participants',
                'address',
            ];
            $fields = array_merge($fields,$added_field);
        }

        return $fields;

    }

    public static function getStatusnames($status){
        if($status==self::APPROVED){
            $data = 'Approved';
        }elseif( $status==self::DECLINED){
            $data = 'DECLINED';
        }elseif( $status==self::DELETED){
            $data = 'Deleted';
        }elseif( $status==self::DRAFT){
            $data = 'Draft';
        }elseif( $status==self::UNDER_REVIEW){
            $data = 'Under Review';
        }else{
            $data = '----';
        }
        return $data;
    }
    public static function getLevelName($level){
        if($level==self::ALL_LEVELS){
            $data = 'All Levels';
        }elseif( $level==self::MIDDLE){
            $data = 'Middle';
        }elseif( $level==self::ADVANCED){
            $data = 'Advanced';
        }elseif( $level==self::BEGINNER){
            $data = 'Beginner';
        }else{
            $data = '----';
        }
        return $data;
    }
    public static function getLanguageName($language){
        if($language==1){
            $data = 'Armenian';
        }else{
            $data = 'English';
        }
        return $data;
    }

    public function calculatePassedNumbers($user_id){
        $totals = $lessons_count = $passed_lessons = $quize_count = 0;
        $learning = CoursesLearning::query()->where('user_id',$user_id)
            ->where('course_id',$this->id)->first();

        $quizes_count_sql  = "select id  from quizzes where section_id IN (select id from sections where course_id = {$this->id})";
        $quizes_count_data = DB::select($quizes_count_sql);
        $quize_count = count($quizes_count_data);


        if(!empty($learning)){
           $lessons = $learning->lessons_status;

           if(!empty($lessons)){
               $lessonsArr = json_decode($lessons,true);
               $lessons_count = count($lessonsArr);
               foreach($lessonsArr as $c){
                   if($c['status'] == 1){
                       $passed_lessons+=1;
                   }
               }

               if(!empty($quizes_count_data)){
                   foreach($quizes_count_data as $q){
                       $quiz_answered  = "select id  from quize_answers where student_id = {$user_id} and quiz_id = {$q->id} ";
                       $r = DB::select($quiz_answered);

                       if(!empty($r)){
                           $passed_lessons+=1;
                       }
                   }
               }
               $totals = $lessons_count+ $quize_count;
              $result_percent = ($totals > 0 )? $passed_lessons*100/$totals:0;
              return round($result_percent, 2).'%';

           }else{
               return '0%';
           }
        }else{
            return '0%';
        }
    }

}
