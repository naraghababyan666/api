<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
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
    ];

    public const SUPER_ADMIN = 1;
    public const TRAINER = 3;
    public const TRAINING_CENTER = 4;
    public const STUDENT = 5;
    public const MODERATOR = 2;
}
