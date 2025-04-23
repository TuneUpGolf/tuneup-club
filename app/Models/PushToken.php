<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushToken extends Model
{
    use HasFactory;
    protected $table = "expo_token";
    protected $guard_name = 'web';
    protected $fillable = ['student_id', 'influencer_id', 'token'];
    public $timestamps = false;

    public function influencer()
    {
        return $this->belongsTo(User::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
