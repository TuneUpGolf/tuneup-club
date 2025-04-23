<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackContent extends Model
{
    use HasFactory;

    protected $table = "feedback_content";
    protected $guard_name = 'web';
    protected $fillable = [
        'id',
        'purchase_video_id',
        'type',
        'thumbnail',
        'url'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
