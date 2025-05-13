<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePost extends Model
{
    use HasFactory;
    protected $table = 'purchasepost';

    protected $fillable = ['follower_id', 'post_id', 'active_status', 'session_id'];

    public function follower()
    {
        return $this->belongsTo(Follower::class);
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
