<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Illuminate\Support\Facades\Storage;

use App\Models\File;
use App\Models\FileSignStampType;
use App\Models\FileSignStamp;

use Validator;

class CreateSignStamp implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;

    public function uniqueId()
    {
        return $this->file->id;
    }

    protected $file;
    private const DOC_EXT = 'odt,ods,odp,csv,txt,xlx,xls,pdf,doc,docx,xlsx,xml,ppt';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }


    protected function strToPdf($str) {
        // mb_convert_encoding($text, 'windows-1251', 'utf-8');
        $f = 'UTF-8//IGNORE';
        $t = 'WINDOWS-1251//IGNORE';

        return '<'.\bin2hex(\iconv($f, $t, $str)).'>';
    }

    protected function fillText($str, $x, $y){
        return "{$x} {$y} moveto {$this->strToPdf($str)} show";
    }

    protected function drawStampFrame($x, $y, $w, $h) {
        $_h = -$h;
        $_w = -$w;
        return "{$x} {$y} moveto {$w} 0 rlineto 0 {$_h} rlineto {$_w} 0 rlineto 0 {$h} rlineto stroke";
    }

    protected function setFont($name, $size = 10) {
        return "{$name} findfont {$size} scalefont setfont";
    }

    protected function setRgbColor($color) {
        return $color.' setrgbcolor';
    }

    /**
     * Преобразовать документ в PDF формат
     *
     *
     */
    protected function toPdf($outdir, $fileName) {
        $process = new Process(['libreoffice', '--convert-to', 'pdf', '--outdir', Storage::path($outdir), Storage::path($fileName)]);

        // throw new \Exception($process->getCommandLine());
        //$process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Создаем страницу с информацией об ЭП
     *
     *
     */
    protected function createFileSignInfoPdf($outfile, $stampParam) {
        $listW = $stampParam['listW'];
        $listH = $stampParam['listH'];
        $fontName = $stampParam['fontName'];
        $color = $stampParam['color'];
        $fontSize = $stampParam['fontSize'];

        $stampX = 15;
        $stampY = $listH - 15;

        $postscript = [];

        $textX = $stampX + 35;
        $textY = $stampY - 30;
        $lineSpacing = 10;
        $textOffset = 0;
        $certs = $this->file->signCerts()->distinct()->get();
        foreach($certs as $cert){
            $validfrom = date('d.m.Y', strtotime($cert->validfrom));
            $validto   = date('d.m.Y', strtotime($cert->validto));

            $postscript[] = $this->fillText('Документ подписан квалифицированной ЭП', $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
            $postscript[] = $this->fillText("Сертификат {$cert->serial_number}", $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
            $postscript[] = $this->fillText("Владелец {$cert->SN} {$cert->G}", $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
            $postscript[] = $this->fillText("Действителен с {$validfrom} по {$validto}", $textX, $textY - $textOffset);
            $textOffset += $lineSpacing * 2;
        }

        $stampW = $listW - 40;
        $stampH = 20 + $textOffset;

        $processCreateStamp = new Process(
            array_merge([
                'gs','-o',Storage::path($outfile),'-sDEVICE=pdfwrite',
                '-g'.$listW.'0x'.$listH.'0',
                '-c',
                $this->setRgbColor($color),
                $this->drawStampFrame($stampX, $stampY, $stampW, $stampH),
                $this->setFont($fontName, $fontSize),
                $this->fillText('ИД оригинала документа в информационной системе ТФОМС Курганской области: '.$this->file->id, 50, $stampY-10)
            ],
            $postscript,
            [
                '-c', 'showpage'
            ]
            )
        );

        $processCreateStamp->run();


        if (!$processCreateStamp->isSuccessful()) {
            throw new ProcessFailedException($processCreateStamp);
        }
    }

    /**
     * Создаем штамп с идентификатором документа
     *
     *
     */
    protected function createFileIdStampPdf($outfile, $stampParam) {
        $processCreateSmallStamp = new Process(['gs',
                '-o', Storage::path($outfile),
                '-sDEVICE=pdfwrite',
                '-g'.$stampParam['listW'].'0x'.$stampParam['listH'].'0',
                '-c',
                $this->setRgbColor($stampParam['color']),
                $this->setFont($stampParam['fontName'],$stampParam['fontSize']),
                $this->fillText('Подписано ЭП. ИД оригинала документа в информационной системе ТФОМС Курганской области: '.$this->file->id, 1, 1),
                '-c', 'showpage'
            ]);

        $processCreateSmallStamp->run();

        if (!$processCreateSmallStamp->isSuccessful()) {
            throw new ProcessFailedException($processCreateSmallStamp);
        }
    }

    /**
     * Добавить штамп на каждую страницу документа
     *
     *
     */
    protected function addMultistampPdf($outfile, $infile, $stampfile) {
        $processPdftkMultistamp = new Process([
                'pdftk', Storage::path($infile),
                'multistamp', Storage::path($stampfile),
                'output', Storage::path($outfile)
            ]);

        $processPdftkMultistamp->run();

        if (!$processPdftkMultistamp->isSuccessful()) {
            throw new ProcessFailedException($processPdftkMultistamp);
        }
    }

    /**
     * Объединить несколько файлов PDF в один
     *
     *
     */
    protected function mergePdf($outfile, $fileArr) {

        $processCreateStampedFile = new Process(array_merge([
                'gs','-dBATCH','-dNOPAUSE','-q',
                '-sDEVICE=pdfwrite',
                '-sOutputFile='.Storage::path($outfile)
            ],
            array_map(function($file) {
               return Storage::path($file);
            }, $fileArr)
        ));
        // $processCreateStampedFile->setTimeout(60);
        try
        {
            $processCreateStampedFile->run();

             if (!$processCreateStampedFile->isSuccessful()) {
             throw new ProcessFailedException($processCreateStampedFile);
            }
        }
        catch(\Symfony\Component\Process\Exception\ProcessSignaledException $ex) //The process has been signaled with signal "11"
        {
            // В редких случаях gs выдает ошибку (вероятно проблема со структурой pdf файла)
            // пробуем объеденить через pdftk
            $processMergePdfFile = new Process(array_merge([
                    'pdftk'
                ],
                array_map(function($file) {
                   return Storage::path($file);
                }, $fileArr),
                [
                    'cat','output',Storage::path($outfile)
                ],
            ));

            $processMergePdfFile->run();

            if (!$processMergePdfFile->isSuccessful()) {
                throw new ProcessFailedException($processMergePdfFile);
            }
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $type_id = FileSignStampType::where('name','additionalPage')->firstOrFail()->id;
        $fileSignStamp = FileSignStamp::firstOrNew([
            'file_id' => $this->file->id,
            'type_id' => $type_id,
            'user_id' => null
        ]);

        $filename  = pathinfo($this->file->file_path, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($this->file->file_path, PATHINFO_EXTENSION));

        $validator = Validator::make([
              'extension' => $extension,
          ],
          [
              'extension' => 'required|in:' . CreateSignStamp::DOC_EXT,
          ]);

        if($validator->fails()){
            return;
        }

        if(empty($fileSignStamp->pdf_file_path) ||
            !Storage::exists($fileSignStamp->pdf_file_path)){
            if($extension === 'pdf')
            {
                // Если документ уже PDF преобразовывать не нужно
                $fileSignStamp->pdf_file_path = $this->file->file_path;
            } else {
                // TODO: Преобразовывать только допустимые форматы
                $pdfFileFolder = 'UserFilesToPdf';
                $this->toPdf($pdfFileFolder, $this->file->file_path);
                $pdfFilePath = $pdfFileFolder.'/'.$filename.'.pdf';
                if(Storage::exists($pdfFilePath)){
                    $fileSignStamp->pdf_file_path = $pdfFilePath;
                } else {
                    throw new \Exception('Файл не существует');
                }
            }
            $fileSignStamp->save();
        }

        // Создаем страницу с информацией об ЭП
        $stampParam = [
            'listW' => 595,
            'listH' => 842,
            'fontName' => '/CourierC.otf',
            'color' => '0 0 1',
            'fontSize' => 10
        ];
        $stampPageFileName = 'CertStamps/'.$filename.'.pdf';
        $this->createFileSignInfoPdf($stampPageFileName, $stampParam);

        if(empty($fileSignStamp->pdf_with_id_file_path) ||
            !Storage::exists($fileSignStamp->pdf_with_id_file_path)){
            // Создаем штамп "ИД оригинала документа"
            $idStampFileName = 'CertStamps/'.$filename.'_idpA4.pdf';
            $this->createFileIdStampPdf($idStampFileName, $stampParam);

            // Добавляем штамп "ИД оригинала документа" на все страницы
            $fileSignStamp->pdf_with_id_file_path = 'UserFilesStamped/'.$filename.'_id.pdf';
            $this->addMultistampPdf(
                $fileSignStamp->pdf_with_id_file_path,
                $fileSignStamp->pdf_file_path,
                $idStampFileName
            );

            Storage::delete($idStampFileName);
            $fileSignStamp->save();
        }

        // Добаляем страницу штампа к документу
        $stampedFileName = 'UserFilesStamped/'.$filename.'.pdf';
        $this->mergePdf($stampedFileName,
            [
                $fileSignStamp->pdf_with_id_file_path,
                $stampPageFileName
            ]
        );
        Storage::delete($stampPageFileName);

        $fileSignStamp->stamped_file_path = $stampedFileName;
        $fileSignStamp->save();
    }
}


/*
// Штамп для одного сертификата
$smallStampPath = Storage::path('CertStamps/'.$cert->thumbprint.'.pdf');
$validfrom = date('d.m.Y', strtotime($cert->validfrom));
$validto   = date('d.m.Y', strtotime($cert->validto));

$processCreateSmallStamp = new Process(['gs',
        '-o', $smallStampPath,
        '-sDEVICE=pdfwrite',
        '-g3200x750',
        '-c', '0 .8 0 0 setcmykcolor',
        '-c', '12 12 moveto',
        '-c', '0 0 0 setrgbcolor',
        '-c', '1 1 moveto',
        '-c', '318 0 rlineto 0 73 rlineto -318 0 rlineto 0 -73 rlineto',
        '-c', 'stroke',
        '-c', '/CourierC.otf findfont 12 scalefont setfont',
        '-c', '50 63 moveto',
        '-c', $this->strToPdf('ДОКУМЕНТ ПОДПИСАН').' show',
        '-c', '50 51 moveto',
        '-c', $this->strToPdf('ЭЛЕКТРОННОЙ ПОДПИСЬЮ').' show',
        '-c', '/CourierC.otf findfont 8 scalefont setfont',
        '-c', '7 39 moveto',
        '-c', $this->strToPdf('Сертификат '.$cert->serial_number).' show',
        '-c', '7 27 moveto',
        '-c', $this->strToPdf('Владелец '.$cert->SN.' '.$cert->G).' show',
        '-c', '7 15 moveto',
        '-c', $this->strToPdf('Действителен с '.$validfrom.' по '.$validto).' show',
        '-c', '/CourierC.otf findfont 6 scalefont setfont',
        '-c', '7 3 moveto',
        '-c', $this->strToPdf('ID документа в системе ТФОМС Курганской области: '.$this->file->id).' show',
        '-c', 'showpage'
    ]);

$processCreateSmallStamp->run();

// executes after the command finishes
if (!$processCreateSmallStamp->isSuccessful()) {
    throw new ProcessFailedException($processCreateSmallStamp);
}
*/

/*
// Размещение штампа на А4
    $processCreateA4Stamp = new Process(['gs',
        '-o', Storage::path('CertStamps/'.$cert->thumbprint.'_pA4.pdf'),
        '-sDEVICE=pdfwrite',
        '-g5950x8420',
        '-c', '<</PageOffset [140 60]>> setpagedevice',
        '-f', $smallStampPath
    ]);
    $processCreateA4Stamp->run();

    // executes after the command finishes
    if (!$processCreateA4Stamp->isSuccessful()) {
        throw new ProcessFailedException($processCreateA4Stamp);
    }
*/
