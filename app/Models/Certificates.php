<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificates extends Model
{
    use HasFactory;
    public $student_name;
    protected $appends = ["student_name"];
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function getStudentNameAttribute(){
        return $this->student_name;
    }
}
