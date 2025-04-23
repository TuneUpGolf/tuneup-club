<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Review extends Model
{
    use HasFactory;

    public $table = 'reviews';
    protected $fillable = [
        'instructor_id',
        'follower_id',
        'rating',
        'review'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function student()
    {
        return $this->belongsTo(Follower::class);
    }
}
