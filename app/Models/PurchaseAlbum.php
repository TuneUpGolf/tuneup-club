<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseAlbum extends Model
{
    use HasFactory;
    protected $table = 'purchase_albums';

    protected $fillable = ['student_id', 'album_category_id', 'active_status', 'session_id'];


    public function albumCategory()
    {
        return $this->belongsTo(AlbumCategory::class);
    }
}
