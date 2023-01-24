<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public const NEW_NOTIFICATION = 0;
    public const SEEN = 1;

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'status',
        'type',
        'item',
        'item_id',
        'message',
        'created_at',
        'updated_at'
    ];
    public $timestamps = false;

    public static function sendNotification($user_id,$title,$message, $type="info", $item, $id){
       Notification::create([
            "user_id" => $user_id,
            "title" => $title,
            "message" =>$message,
            "status" => 0,
            "type" =>$type,
            "item" => $item,
            "item_id" => $id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }


}
