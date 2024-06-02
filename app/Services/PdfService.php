<?php
declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Illuminate\Support\Facades\Storage;

class PdfService {

    public function createDocument($outfile, $lines, $params) {
        $listW = $params['listW'];
        $listH = $params['listH'];
        $fontName = $params['fontName'];
        $color = $params['color'];
        $fontSize = $params['fontSize'];
        $perPage = $params['perPage'];

        $postscript = [
            'gs',
            '-o', Storage::path($outfile),
            '-sDEVICE=pdfwrite',
            '-g'.$listW.'0x'.$listH.'0',
            '-c',
            $this->setRgbColor($color),
            $this->setFont($fontName,$fontSize)
        ];

        $textX = 35;
        $textY = $listH - 30;
        $lineSpacing = 12;
        $textOffset = 0;

        $i = 0;
        foreach ($lines as $l) {
            $i++;
            if ($i % $perPage === 0) {
                $textOffset = 0;
                $postscript[] = '-c';
                $postscript[] = 'showpage';
                $postscript[] = $this->setRgbColor($color);
                $postscript[] = $this->setFont($fontName,$fontSize);
            }
            $postscript[] = $this->fillText($l, $textX, $textY - $textOffset);
            $textOffset += $lineSpacing;
        }
        $postscript[] = '-c';
        $postscript[] = 'showpage';

        $processCreateSmallStamp = new Process($postscript);

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
