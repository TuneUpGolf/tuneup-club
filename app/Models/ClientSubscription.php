<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubscription extends Model
{
    use HasFactory;

    protected $table = 'client_subscriptions';

    protected $fillable = [
        'follower_id',
        'plan_id',
        'influencer_id',
        'tenant_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
    ];

    public function follower()
    {
        return $this->belongsTo(Follower::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
