<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSubscriptionDetail extends Model
{
    use HasFactory;

    protected $table = 'client_subscription_details';

    protected $fillable = [
        'client_subscription_id',
        'invoice_id',
        'payment_intent_id'
    ];

    public function clientSubscription()
    {
        return $this->belongsTo(ClientSubscription::class);
    }
}
