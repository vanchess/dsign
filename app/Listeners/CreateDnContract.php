<?php

namespace App\Listeners;

use App\Events\MessageStatusChecked;
use App\Models\Message;
use App\Models\MessageType;
use App\Services\CreateDnContractService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDnContract implements ShouldQueue
{
    use InteractsWithQueue;

    private $createDnContractService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(CreateDnContractService $createDnContractService)
    {
        $this->createDnContractService = $createDnContractService;
    }

    /**
     * Handle the event.
     *
     * @param  App\Events\MessageStatusChecked  $event
     * @return void
     */
    public function handle(MessageStatusChecked $event)
    {
        if ($event->msgType === 'dn-contract' && $event->newStatusName == 'ready') {
            $msg = Message::findOrFail($event->msgId);
            $this->createDnContractService->createDnContract(
                $msg->id,
                $msg->subject,
                $msg->organization_id,
                $msg->text
            );
        }
    }
}
