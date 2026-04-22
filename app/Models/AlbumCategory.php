<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PDO;

class AlbumCategory extends Model
{
    use HasFactory;
    protected $table = 'album_categories';
    protected $fillable = [
        'instructor_id',
        'tenant_id',
        'title',
        'slug',
        'description',
        'payment_mode',
        'price',
        'image',
        'locked_thumbnail',
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

    public function posts()
    {
        return $this->hasMany(Post::class, 'album_category_id');
    }

    public function purchaseAlbum()
    {
        return $this->hasOne(PurchaseAlbum::class)->where([
            ['student_id', $this->student_id],
            ['album_category_id', $this->album_category_id]
        ]);
    }
}
