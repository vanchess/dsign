<?php

namespace App\Listeners;

use App\Events\UserRoleAdd;
use App\Models\User;
use App\Services\MessageForwardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ForwardMsg implements ShouldQueue
{
    public $timeout = 300;
    public $tries = 5;

    private $messageForwardService;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(MessageForwardService $messageForwardService)
    {
        $this->messageForwardService = $messageForwardService;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\UserRoleAdd  $event
     * @return void
     */
    public function handle(UserRoleAdd $event)
    {
        if ($event->roleName === 'mo-lider' || $event->roleName === 'mo-chief-accountant') {
            //$from = new \DateTime('-6 month');
            $from = new \DateTime('01-01-2021');
            $user = User::find($event->userId);

            if ($event->roleName === 'mo-lider') {
                $this->messageForwardService->forwardOrganizationMessage($user->organization_id, [2,3,4,5,6,7,8,9,10,12], [$event->userId], $from);
            }
            if ($event->roleName === 'mo-chief-accountant') {
                $this->messageForwardService->forwardOrganizationMessage($user->organization_id, [2,5,6], [$event->userId], $from);
            }
        }
    }
}
