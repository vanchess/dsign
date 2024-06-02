<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserChangedMessageStatus;
use App\Services\PdfService;
use Illuminate\Contracts\Queue\ShouldQueue;

// use Illuminate\Queue\InteractsWithQueue;

class OnChangeMsgStatusByUser implements ShouldQueue
{
    public $timeout = 300;
    public $tries = 5;

    private $pdfService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(PdfService $service)
    {
        $this->pdfService = $service;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UserChangedMessageStatus $event)
    {
        if ($event->msgType == 'displist' || $event->statusName == 'sent') {
            $stampParam = [
                'listW' => 842,
                'listH' => 595,
                'fontName' => '/CourierC.otf',
                'color' => '0 0 0',
                'fontSize' => 10,
                'perPage' => 47
            ];

            $lines = [];
            for ($i = 0; $i < 420; $i++) {
                $lines[] = '5555555555555555	04109551121	1';
            }

            $this->pdfService->createDocument('outFile1234567890.pdf', $lines, $stampParam);
        }
    }
}
