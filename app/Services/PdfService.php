<?php
declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Illuminate\Support\Facades\Storage;

class PdfService {

    public function сreateFileIdStampPdf($outfile, $stampParam) {

        $postscript = [];

        $textX = 35;
        $textY = 30;
        $lineSpacing = 10;
        $textOffset = 0;

        for($i = 0; $i < 1000; $i++) {

            $postscript[] = $this->fillText('Документ подписан квалифицированной ЭП', $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
            $postscript[] = $this->fillText("Сертификат ", $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
            $postscript[] = $this->fillText("Владелец ", $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
            $postscript[] = $this->fillText("Действителен с ", $textX, $textY - $textOffset);
            $textOffset += $lineSpacing * 2;
        }

        $processCreateSmallStamp = new Process(array_merge(
            ['gs',
                '-o', Storage::path($outfile),
                '-sDEVICE=pdfwrite',
                '-g'.$stampParam['listW'].'0x'.$stampParam['listH'].'0',
                '-c',
                $this->setRgbColor($stampParam['color']),
                $this->setFont($stampParam['fontName'],$stampParam['fontSize']),
                $this->fillText('Подписано ЭП. ИД оригинала документа в информационной системе ТФОМС Курганской области: ', 1, 1)
            ],
            $postscript,
            [
                '-c', 'showpage'
            ]

        ));

        $processCreateSmallStamp->run();

        if (!$processCreateSmallStamp->isSuccessful()) {
            throw new ProcessFailedException($processCreateSmallStamp);
        }
    }

    protected function setRgbColor($color) {
        return $color.' setrgbcolor';
    }

    protected function setFont($name, $size = 10) {
        return "{$name} findfont {$size} scalefont setfont";
    }

    protected function fillText($str, $x, $y){
        return "{$x} {$y} moveto {$this->strToPdf($str)} show";
    }

    protected function strToPdf($str) {
        // mb_convert_encoding($text, 'windows-1251', 'utf-8');
        $f = 'UTF-8//IGNORE';
        $t = 'WINDOWS-1251//IGNORE';

        return '<'.\bin2hex(\iconv($f, $t, $str)).'>';
    }
}
