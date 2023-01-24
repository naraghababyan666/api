<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Category extends Model
{
    use  HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'ordering',
        'icon',
    ];
    protected $appends =["title"];
    protected $hidden=["translation"];
    public $timestamps = false;
    public static $language;

    public static function isExists($id)
    {
        return Category::where('id', $id)->exists();
    }

    public function translation()
    {
        return $this->hasOne(CategoryTranslation::class, 'category_id', 'id')->where('language_id', self::$language);
    }

    public function getTitleAttribute()
    {
        return $this->translation->title??"";
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id')->with(["children"]);

    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id')->with(["parent"]);

    }

    public function course(){
        return $this->hasMany(Course::class);
    }
}
