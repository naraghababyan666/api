<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    use HasFactory;
    public $timestamps = false;
    public function getAvatarAttribute()
    {

        return   isset($this->attributes['avatar']) ? env("APP_URL") . "/" . $this->attributes['avatar'] : null;
    }

}
