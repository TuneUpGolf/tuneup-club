<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostLesson extends Pivot
{
    use HasFactory;

    protected $table = 'post_lesson';

    protected $fillable = [
        'post_id',
        'lesson_id',
        'created_by',
    ];

}
