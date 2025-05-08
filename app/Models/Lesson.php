<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Lesson extends Model
{
    use BelongsToTenant;
    //product
    protected $table      = "lessons";
    protected $guard_name = 'web';

    const LESSON_TYPE_INPERSON  = 'inPerson';
    const LESSON_TYPE_ONLINE    = 'online';
    const LESSON_PAYMENT_CASH   = 'cash';
    const LESSON_PAYMENT_ONLINE = 'online';
    const LESSON_PAYMENT_BOTH   = 'both';

    const TYPE_MAPPING = [
        "inPerson" => "In-Person",
        "online"   => "Online",
    ];

    protected $fillable = [
        'lesson_name',
        'lesson_description',
        'lesson_price',
        'lesson_quantity', // follower can upload video, influencer will provide feedback. This field will decide how many videos will the influencer give feedback on in the given price.
        'required_time',
        'created_by',
        'detailed_description',
        'active_status',
        'type',
        'payment_method',
        'lesson_duration',
        'max_followers',
        'is_package_lesson',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slots::class, 'lesson_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'lesson_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_lesson')
            ->withPivot('created_by', 'type')
            ->withTimestamps();
    }
}
