<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikeAlbum extends Model
{
    use HasFactory;
    public $table = 'like_albums';
    protected $fillable = [
        'instructor_id',
        'student_id',
        'album_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
