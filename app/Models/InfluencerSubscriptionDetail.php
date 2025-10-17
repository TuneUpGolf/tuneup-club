<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerSubscriptionDetail extends Model
{
    use HasFactory;

     protected $table = 'influencer_subscription_details';

    protected $fillable = [
        'influencer_subscription_id',
        'invoice_id',
        'payment_intent_id'
    ];
}
