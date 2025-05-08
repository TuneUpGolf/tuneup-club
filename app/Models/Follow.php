<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;
    public $table = 'follows';
    protected $fillable = ['follower_id', 'influencer_id', 'isPaid', 'active_status', 'session_id', 'subscription_id'];

    public const FOLLOW = 0;
    public const SUBSCRIPTION = 1;

    public function student()
    {
        return $this->belongsTo(Follower::class);
    }
    public function instructor()
    {
        return $this->belongsTo(User::class);
    }
}
