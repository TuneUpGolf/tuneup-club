<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Review extends Model
{
    use HasFactory;

    public $table = 'reviews';
    protected $fillable = [
        'influencer_id',
        'student_id',
        'rating',
        'review'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
