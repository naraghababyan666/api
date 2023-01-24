<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizeAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_id',
        'question_id',
        'answer'

    ];
}
