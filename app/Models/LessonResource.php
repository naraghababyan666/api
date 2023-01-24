<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Model
{
    use HasFactory;
    protected $fillable = [
        'resource_id',
        'lesson_id',
    ];
    public $timestamps = false;
    protected $appends =["title","id","path"];
    protected $hidden=["resource_id","lesson_id","resource"];
    public function resource()
    {
        return $this->hasOne(Resource::class, 'id',"resource_id");
    }
    public function getTitleAttribute()
    {
        return $this->resource->title??"";
    }
    public function getIdAttribute()
    {
        return $this->resource->id??"";
    }
    public function getPathAttribute()
    {
        return $this->resource->path??"";
    }
}
