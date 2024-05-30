<?php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserChangedMessageStatus
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $msgId;
    public $msgType;
    public $statusName;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $msgId, string $msgType, string $statusName, int $userId)
    {
        $this->msgId = $msgId;
        $this->statusName = $statusName;
        $this->userId = $userId;
        $this->msgType = $msgType;
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
