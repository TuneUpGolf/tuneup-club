<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use App\Models\Payment;

class Purchase extends Model
{
    use BelongsToTenant;
    //sales
    protected $table = "purchases";

    protected $fillable = [
        'id',
        'tenant_id',
        'follower_id',
        'influencer_id',
        'coupon_id',
        'lesson_id', // ammount of purchase can be deduced from lesson_id
        'isFeedbackComplete',
        'slot_id',
        'total_amount',
        'friend_names',
    ];
    protected $guarded = [
        'status', // Completed or not
        'lessons_used' //lesson quantity - used (lesson_quantity - purchase_videos->count());
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_INCOMPLETE = "incomplete";
    const STATUS_COMPLETE = "complete";

    const STATUS_MAPPING = [
        "incomplete" => "Incomplete",
        "complete"  => "Completed",
        "inprogress" => "InComplete",
    ];

    public function follower()
    {
        return $this->belongsTo(\App\Models\Follower::class, 'follower_id');
    }

    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slots::class);
    }

    public function videos()
    {
        return $this->hasMany(PurchaseVideos::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
