<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnotationVideos extends Model
{
    use HasFactory;

    protected $table = 'annotation_videos';

    protected $fillable = [
        'id',
        'uuid',
        'influencer_id',
        'video_url',
    ];
}
