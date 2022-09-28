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

class UserRoleAdd
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $roleName;
    public $assignedByUserId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $userId, string $roleName, int $assignedByUserId)
    {
        $this->userId = $userId;
        $this->roleName = $roleName;
        $this->assignedByUserId = $assignedByUserId;
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
