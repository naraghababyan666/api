<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'path',
        'user_id',
    ];
    public function getPathAttribute()
    {
        return   isset($this->attributes['path']) ? env("APP_URL") . "/" . $this->attributes['path'] : null;
    }

}
