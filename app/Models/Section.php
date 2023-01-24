<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'course_id',
    ];

    public function quiz()
    {
        return $this->hasMany(Quiz::class,'section_id', 'id');
    }
    public function lessons()
    {
        return $this->hasMany(SectionLesson::class, 'section_id', 'id');
    }

}
