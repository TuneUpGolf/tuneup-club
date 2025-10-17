<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerSubscription extends Model
{
    use HasFactory;

      protected $table = 'influencer_subscriptions';

    protected $fillable = [
        'plan_id',
        'influencer_id',
        'tenant_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
    ];
}
