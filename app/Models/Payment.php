<?php

namespace App\Models;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Payment extends Model
{
    use BelongsToTenant;

    public $table = 'payment';
    protected $guard_name = 'web';

    protected $fillable = [
        'id',
        'tenant_id',
        'method',
        'date',
        'status',
        'purchase_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }


    const PAYMENT_STATUS_CONFIRM = 'confirmed';
    const PATMENT_STATUS_NOT_CONFIRMED = ' uncofirmed';
}
