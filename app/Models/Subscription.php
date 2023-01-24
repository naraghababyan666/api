<?php

namespace App\Models;

use App\Mail\NewCourseMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email'
    ];

    public static function sendEmailAboutNewCourse($course)
    {

        $subscribers = Subscription::query()->pluck('email')->toArray();
        if (!empty($subscribers)) {
            Mail::to($subscribers)->send(new NewCourseMail($course));
        }


    }
}
