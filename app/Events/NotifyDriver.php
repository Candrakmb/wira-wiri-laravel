<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyDriver implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $driver;
    public $order;
    /**
     * Create a new event instance.
     */
    public function __construct($driver,$order)
    {
        $this->driver = $driver;
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('driver.'.$this->driver->id);
    }
}
