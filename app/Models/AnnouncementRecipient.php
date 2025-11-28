<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementRecipient extends Model
{
    use HasFactory;

     protected $fillable = [
        'announcement_id',
        'follower_id',
        'is_read',
        'read_at'
    ];

      protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function student()
    {
        return $this->belongsTo(Follower::class, 'follower_id');
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}
