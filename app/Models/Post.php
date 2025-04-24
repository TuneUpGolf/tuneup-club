<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    public $table       = 'post';
    protected $fillable = [
        'influencer_id',
        'follower_id',
        'title',
        'description',
        'isFollowerPost',
        'file_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function influencer()
    {
        return $this->belongsTo(User::class);
    }
    public function student()
    {
        return $this->belongsTo(Follower::class);
    }
    public function likePost(): HasMany
    {
        return $this->hasMany(LikePost::class);
    }
    public function reportPost(): HasMany
    {
        return $this->hasMany(ReportPost::class);
    }
    public function purchasePost(): HasMany
    {
        return $this->hasMany(PurchasePost::class);
    }
}
