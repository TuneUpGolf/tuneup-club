<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    protected $table = 'albums';
    protected $fillable = [
        'instructor_id',
        'tenant_id',
        'album_category_id',
        'title',
        'description',
        'media',
        'status',
        'slug',
        'file_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(AlbumCategory::class,'album_category_id','id');
    }

    public function likeAlbum()
    {
        return $this->hasMany(LikeAlbum::class);
    }

}