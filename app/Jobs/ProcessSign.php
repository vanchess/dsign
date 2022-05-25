<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

use App\Models\FileSign;
use App\Models\File;
use App\Models\CryptoCert;
use Illuminate\Support\Facades\Storage;

use App\Jobs\CreateSignStamp;
use App\Jobs\CheckMessageStatus;
/*
function SetupStore($location, $name, $mode)
{
    $store = new CPStore();
    $store->Open($location, $name, $mode);
    return $store;
}

function SetupCertificates($location, $name, $mode)
{
    $store = SetupStore($location, $name, $mode);
    return $store->get_Certificates();
}

function SetupCertificate($location, $name, $mode,
                           $find_type, $query, $valid_only,
                           $number)
{
    $certs = SetupCertificates($location, $name, $mode);
    
    if(!is_null($find_type)) 
    {
        $certs = $certs->Find($find_type, $query, $valid_only);
        if (is_string($certs)) {
            return $certs;
        } else {
            return $certs->Item($number);
        }
    }
    else
    {
        $cert = $certs->Item($number);
        return $cert;
    }
}
*/

function CheckQuotes($str)
{
    $result = 0;
    $result = mb_substr_count($str, '"');
    return !($result%2);
}

function CertGetProperty($str, $what)
{
    $prop = "";

    $begin = mb_strpos($str, $what, 0);

    if($begin !== false)
    {
        $begin += mb_strlen($what);
        $end = mb_strpos($str, ', ', $begin);
        while($end !== false) {
            $prop = mb_substr($str, $begin, $end-$begin);
            if (CheckQuotes($prop))
                return $prop;
            $end = mb_strpos($str, ', ', $end + 1);
        }
        $prop = mb_substr($str, $begin);
    }

    return $prop;
}

class ProcessSign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    
    protected $sign;
     
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FileSign $sign)
    {
        $this->sign = $sign;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        // ini_set("log_errors", 1); 
        // ini_set ('display_errors', 1); 
        // error_reporting (E_ALL); 
        
        $file = File::find($this->sign->file_id);
        
        $this->sign->verified_on_server_success = false;
        try
        {
            // $tsp_addres = "http://testca.cryptopro.ru/tsp/tsp.srf";
            //$cert = SetupCertificate(CURRENT_USER_STORE, "My", STORE_OPEN_READ_ONLY,
            //                         CERTIFICATE_FIND_SUBJECT_NAME, "test", 0,
            //                         1);
            
            //if (!$cert)
            //{
            //    return "Certificate not found\n";
            //}

            //$signer = new \CPSigner();
            //$signer->set_TSAAddress($tsp_addres);
            //$signer->set_Certificate($cert);

            $content = base64_encode(Storage::get($file->file_path));
            $sd = new \CPSignedData();
            $sd->set_ContentEncoding(BASE64_TO_BINARY);
            $sd->set_Content($content);
            
            // Проверка отсоединенной подписи (параметр true - отсоединенная)
            $result = $sd->VerifyCades($this->sign->base64, \CADES_BES, true);
            
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
            
            DB::transaction(function () use ($thumbprint, $serialNumber, $validFrom, $validTo, $sn, $issuer, &$cryptoCert) {
                $cryptoCert = CryptoCert::where('thumbprint',$thumbprint)->first(); //->lockForUpdate()
                if ($cryptoCert != null) {
                    ;
                } else {
                    $cryptoCert = CryptoCert::create([
                        'thumbprint' => $thumbprint,
                        'serial_number' => $serialNumber,
                        'validfrom' => $validFrom,
                        'validto' => $validTo,
                        'CN' => CertGetProperty($sn, 'CN='),
                        'SN' => CertGetProperty($sn, 'SN='),
                        'G' => CertGetProperty($sn, 'G='),
                        'T' => CertGetProperty($sn, ' T='),
                        'OU' => CertGetProperty($sn, 'OU='),
                        'O' => CertGetProperty($sn, 'O='),
                        'STREET' => CertGetProperty($sn, 'STREET='),
                        'L' => CertGetProperty($sn, 'L='),
                        'S' => CertGetProperty($sn, 'S='),
                        'C' => CertGetProperty($sn, 'C='),
                        'E' => CertGetProperty($sn, 'E='),
                        'OGRN' => CertGetProperty($sn, 'OGRN='),
                        'SNILS' => CertGetProperty($sn, 'SNILS='),
                        'INN' => CertGetProperty($sn, 'INN='),
                        'issuer' => $issuer,
                        //'description' => bcrypt
                    ]);
                }
            }, 3);
            

            $this->sign->cert_id = $cryptoCert->id;
            $this->sign->verified_on_server_error_srt = null;
            $this->sign->verified_on_server_success = true;
            
            // В связи с изменением 63-ФЗ. В октябре 2022 можно удалить эту проверку.
            $blockCertIssuer = 'CN=ФОМС, O=ФОМС, STREET="Новослободская ул., д.37", L=Москва, S=77 Москва, C=RU, INN=007727032382, OGRN=1027739712857, E=ucfoms@ffoms.ru';
            $blockCertIssuerMsg = 'Применение сертификатов ЭП, созданных удостоверяющими центрами(УЦ), не прошедшими аккредитацию по новым правилам, после 1.01.2022 г. не допускается. Согласно ч. 5 статьи 3 ФЗ №476-ФЗ Аккредитация УЦ, полученная до дня вступления в силу ФЗ №476-ФЗ, действует не более чем до 1.01.22г.';
            if($issuer === $blockCertIssuer) {
                $this->sign->verified_on_server_success = false;
                $this->sign->verified_on_server_error_srt = $blockCertIssuerMsg;
            }
        }
        catch (\Exception $e)
        {
            $this->sign->verified_on_server_error_srt =$e->getMessage();
        }

        $this->sign->verified_on_server_at = now();
        $this->sign->save();
        
        CreateSignStamp::dispatch($file);
        
        /**/
        foreach ($file->messages()->get() as $msg) {
            CheckMessageStatus::dispatch($msg);
        }
        
        return;

        
    }
}
