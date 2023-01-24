<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'icon',
        'ordering',
        'is_default',
    ];

    public static function isExists($id)
    {
        return Language::where('id', $id)->exists();
    }

    public static function getLanguage($code="hy")
    {
        $language = Language::where('short_tag', $code)->first();

        return $language->id;

    }
}

