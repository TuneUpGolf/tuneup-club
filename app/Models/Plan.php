<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'price',
        'price_quarter',
        'price_year',
        'duration',
        'max_users',
        'max_roles',
        'max_influencers',
        'max_documents',
        'max_blogs',
        'discount',
        'durationtype',
        'description',
        'tenant_id',
        'active_status',
        'discount_setting',
        'is_chat_enabled',
        'is_feed_enabled',
        'influencer_id',
        'stripe_price_id',
        'stripe_price_quarter_id',
        'stripe_price_year_id',
        
        'stripe_product_id',
        'lesson_limit'
    ];

    /**
     * Orders (purchases) associated with this plan.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'plan_id');
    }

    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id', 'id');
    }

     public function getLessonLimitLabelAttribute()
    {
        return match ($this->lesson_limit) {
            -1 => 'Unlimited lessons/month',
            default => "{$this->lesson_limit} lessons/month",
        };
    }
}
