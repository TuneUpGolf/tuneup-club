<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPost extends Model
{
    use HasFactory;

    public $table       = 'report_posts';
    protected $fillable = [
        'influencer_id',
        'follower_id',
        'post_id',
        'comment',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }
    public function student()
    {
        return $this->belongsTo(Follower::class);
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
