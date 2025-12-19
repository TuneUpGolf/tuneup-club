<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TenantNotificationEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    public $tenantId;
    public $receiverId;
    public $message;
    public $type; // 🔹 Add type property
    public $sender; // 🔹 Add type property

    /**
     * Create a new event instance.
     *
     * @param string $tenantId
     * @param string|int $receiverId
     * @param mixed $message
     * @param string $type
     */
    public function __construct($tenantId, $receiverId, $message, $type, $sender)
    {
        $this->tenantId   = $tenantId;
        $this->receiverId = $receiverId;
        $this->message    = $message;
        $this->type       = $type; // 🔹 Initialize type
        $this->sender     = $sender;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        // 🔹 Include type dynamically in channel name
        return new Channel("tenant.{$this->tenantId}.{$this->type}.user.{$this->receiverId}");
    }

    /**
     * Get the event name for broadcasting.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'tenant.notification';
    }
}
