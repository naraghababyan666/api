<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount', 'transaction_id', 'status', 'trainer_id'
    ];
protected $appends=["date"];
    public function trainer(){
        $this->belongsTo(User::class, 'trainer_id', 'id');
    }
    public function getDateAttribute(){
  return date("m",strtotime($this->attributes["created_at"]));
    }
}
