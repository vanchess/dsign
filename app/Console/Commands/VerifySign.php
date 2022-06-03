<?php
//declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\File;
use App\Models\FileSign;
use Illuminate\Support\Facades\Storage;

class VerifySign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cryptopro:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $sign;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        //$this->sign = FileSign::find(97854);
        $this->sign = FileSign::find(98168);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $file = File::find($this->sign->file_id);

        $this->info($file->file_path);
        $content = \base64_encode(Storage::get($file->file_path));

        $sd = new \CPSignedData();
        $sd->set_ContentEncoding(BASE64_TO_BINARY);
        $sd->set_Content($content);

        $this->info($content);
        $this->info(BASE64_TO_BINARY);
        $this->info(\CADES_BES);
        $this->info($this->sign->base64);
        $this->info('start VerifyCades');
/*
        $VerifyCades = new \ReflectionMethod(\CPSignedData::class, 'VerifyCades');
        $parameters = $VerifyCades->getParameters();
        foreach ($parameters as $parameter) {
            $this->info((string) $parameter->getType());
        }
*/
        // Проверка отсоединенной подписи (параметр true - отсоединенная)
        $result = $sd->VerifyCades($this->sign->base64, \CADES_BES, true);

        $this->info('finish VerifyCades');
        /*
        // Получение сведений о подписи и сертификате
        $signers = $sd->get_Signers();
        $signer  = $signers->get_Item(1);

        $signingTime = $signer->get_SigningTime();
        $this->sign->signing_time = \DateTime::createFromFormat("d.m.Y H:i:s", $signingTime);
        // get_SignatureTimeStampTime

        $cert = $signer->get_Certificate();
        $serialNumber = $cert->get_SerialNumber();
        $thumbprint = $cert->get_Thumbprint();
        $sn = $cert->get_SubjectName();
        $issuer = $cert->get_IssuerName();

        $validFrom = $cert->get_ValidFromDate();
        $validFrom = \DateTime::createFromFormat("d.m.Y H:i:s", $validFrom);
        // $validFrom = $validFrom->getTimestamp();

        $validTo = $cert->get_ValidToDate();
        $validTo = \DateTime::createFromFormat("d.m.Y H:i:s", $validTo );
        // $validTo = $validTo->getTimestamp();

*/
    }
}
