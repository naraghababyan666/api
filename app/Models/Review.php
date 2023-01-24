<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'course_id', 'rate',"message"
    ];
    protected $appends =["date"];
    protected $hidden=["created_at","updated_at"];
    protected $table = 'reviews';

    public function user(){
        return $this->belongsTo(User::class)->select(array('id', 'first_name', 'last_name', 'avatar'));
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }
    public function getDateAttribute(){
        return   Str::replace("before","ago",Carbon::parse($this->updated_at)->diffForHumans(now())) ;
    }
}
