<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourses extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'course_id',
        'status'
    ];

    // Course statuses
    public const PROGRESS = 1;
    public const FINISHED = 2;

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }
}
