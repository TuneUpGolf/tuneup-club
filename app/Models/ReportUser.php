<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ReportUser extends Model
{
    use HasFactory;

    public $table = 'report_user';
    protected $fillable = [
        'influencer_id',
        'student_id',
        'comment',
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
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
