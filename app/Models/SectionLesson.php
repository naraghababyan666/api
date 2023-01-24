<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionLesson extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [

        'lesson_id',
        'section_id',
    ];
    protected $appends =["title","id","position","video_url","article","description","resources","type"];
    protected $hidden=["lesson","lesson_id"];
    public function section()
    {
        return $this->hasMany(Section::class, "section_id", "id");
    }

    public function lesson()
    {
        return $this->hasOne(Lesson::class, "id", "lesson_id");
    }
    public function getTitleAttribute()
    {
        return $this->lesson->title??"";
    }
    public function getIdAttribute()
    {
        return $this->lesson->id??"";
    }
    public function getPositionAttribute()
    {
        return $this->lesson->position??"";
    }
    public function getVideoUrlAttribute()
    {
        return $this->lesson->video_url??"";
    }
    public function getArticleAttribute()
    {
        return $this->lesson->article??"";
    }
    public function getDescriptionAttribute()
    {
        return $this->lesson->description??"";
    }
    public function getResourcesAttribute()
    {
        return $this->lesson->resources??"";
    }
    public function getTypeAttribute()
    {
        return $this->lesson->type??null;
    }
}
