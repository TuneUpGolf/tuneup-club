<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Slots extends Model
{
    use BelongsToTenant;

    protected $table      = 'slots';
    protected $guard_name = 'web';
    public $timestamps    = false;
    protected $date_time  = ['datetime_column'];
    protected $with       = ['follower'];

    protected $fillable = [
        'lesson_id',
        'follower_id',
        'date_time',
        'location',
        'is_completed',
        'is_active',
        'cancelled',
    ];

    public function lesson()
    {
        return $this->belongsTo(\App\Models\Lesson::class, 'lesson_id');
    }
    // Relationship with followers
    public function follower(): BelongsToMany
    {
        return $this->belongsToMany(Follower::class, 'follower_slots', 'slot_id', 'follower_id')
            ->withPivot(['isFriend', 'friend_name', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    // Check if the slot is fully booked
    public function isFullyBooked(): bool
    {
        return $this->follower()->count() >= $this->lesson->max_followers;
    }

    public function availableSeats(): int
    {
        return $this->lesson->max_followers - $this->follower()->count();
    }
}
