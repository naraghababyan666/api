<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerateCertificate extends Model
{
    use HasFactory;

    public function certificate()
    {
        return $this->belongsTo(Course::class, 'certificate_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
