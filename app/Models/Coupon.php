<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'discount_type',
        'code',
        'discount',
        'limit',
        'description',
    ];

    public function purchase(): HasMany
    {
        return $this->hasMany(Purchase::class, 'coupon_id');
    }

    public function used_coupon()
    {
        return $this->hasMany('App\Models\UserCoupon', 'coupon', 'id')->count();
    }
}
