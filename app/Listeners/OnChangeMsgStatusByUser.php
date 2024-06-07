<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserChangedMessageStatus;
use App\Models\File;
use App\Models\Message;
use App\Models\MessageStatus;
use App\Models\PreventiveMedicalMeasureTypes;
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

            $pmm = PreventiveMedicalMeasureTypes::all()->pluck('name','id');
            $msg = Message::findOrFail($event->msgId);
            $lines = [
                "Список сотрудников на проф.мероприятия.",
                "Медицинская организация: {$msg->organization->name}",
                "(Дополнительная информация: $msg->text)",
                ""
            ];
            $entries = $msg->displists[0]->entries()->orderBy('order')->get();
            foreach ($entries as $e) {
                $t = $pmm[$e->preventive_medical_measure_id];
                $lines[] = "$e->last_name $e->first_name $e->middle_name $e->birthday ЕНП:$e->enp СНИЛС:$e->snils $t $e->description $e->contact_info";
            }
            $path = 'Displist';
            $extension = 'pdf';
            $filename = $path . DIRECTORY_SEPARATOR . uniqid('',true).'.'.$extension;

            $this->pdfService->createDocument($filename, $lines, $stampParam);

            $fileModel = new File();
            $fileModel->name = 'Список сотрудников на проф.мероприятия.'.$extension;
            $fileModel->file_path   = $filename;
            $fileModel->user_id     = $event->userId;
            $fileModel->description = '';
            $fileModel->save();

            $msg->files()->attach($fileModel);
            $msg->status_id = MessageStatus::where('name', 'signing')->first()->id;
            $msg->save();
        }
    }
}
