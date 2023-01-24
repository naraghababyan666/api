<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';
    protected $fillable = [
        'course_id',
        'user_id',
        'old_value',
        'new_value',
        'created_at',
        'updated_at'
    ];
    use HasFactory;
}
