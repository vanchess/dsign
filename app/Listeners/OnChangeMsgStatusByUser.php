<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserChangedMessageStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Queue\InteractsWithQueue;

class OnChangeMsgStatusByUser implements ShouldQueue
{
    public $timeout = 300;
    public $tries = 5;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UserChangedMessageStatus $event)
    {
        //
    }
}
