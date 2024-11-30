<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyKedai implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $orderDestination;
    /**
     * Create a new event instance.
     */
    public function __construct($orderDestination)
    {
        $this->orderDestination = $orderDestination;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notif_kedai.'.$this->orderDestination->kedai->user_id);
    }
}
