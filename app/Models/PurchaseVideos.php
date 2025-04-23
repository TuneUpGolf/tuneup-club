<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PurchaseVideos extends Model
{
    use BelongsToTenant;

    protected $table = "purchasevideos";
    protected $guard_name = 'web';
    protected $fillable = [
        'id',
        'tenant_id',
        'purchase_id',
        'video_url',
        'video_url_2',
        'note',
        'thumbnail',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function feedbackContent(): HasMany
    {
        return $this->hasMany(FeedbackContent::class, 'purchase_video_id');
    }
}
