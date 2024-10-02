<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatusChecked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $msgId;
    public $msgType;
    public $oldStatusName;
    public $newStatusName;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $msgId, string $msgType, string $oldStatusName, string $newStatusName)
    {
        $this->msgId = $msgId;
        $this->msgType = $msgType;
        $this->oldStatusName = $oldStatusName;
        $this->newStatusName = $newStatusName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
