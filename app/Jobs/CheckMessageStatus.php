<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\Message;
use App\Models\MessageStatus;

class CheckMessageStatus implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 1;
    
    public function uniqueId()
    {
        return $this->msg->id;
    }

    protected $msg;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->msg = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $msg = $this->msg;
        
        $statusReady = MessageStatus::where('name','ready')->firstOrFail();
        $statusRejected = MessageStatus::where('name','rejected')->firstOrFail();
        if (
            $msg->status_id === $statusReady->id || 
            $msg->status_id === $statusRejected->id
        ) {
            return;
        }
        
        $statusNoFiles = MessageStatus::where('name','no_files')->firstOrFail();
        $files = $msg->files()->with(['signUsers'])->get();
        if ($files->count() == 0) {
            $msg->status_id = $statusNoFiles->id;
            $msg->save();
            return;
        }
        
        $statusSent = MessageStatus::where('name','sent')->firstOrFail();
        $statusSignedBySpecialist = MessageStatus::where('name','signed_by_specialist')->firstOrFail();
        $statusSignedByHead = MessageStatus::where('name','signed_by_head')->firstOrFail();
        $statusSigning = MessageStatus::where('name','signing')->firstOrFail();
        $statusSignedMo = MessageStatus::where('name','signed_mo')->firstOrFail();
        
        if ($msg->type->name === 'mek') {
            $specialist = true;
            $head = true;
            $mo = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $specialist = $specialist 
                    && ($signUsers->contains('id', 89) || $signUsers->contains('id', 90));
                $head = $head && ($signUsers->contains('id', 11) || $signUsers->contains('id', 88));
                
                $tempMo = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider mek')) {
                        $tempMo = true;
                    }
                }
                $mo = $mo && $tempMo;
            }
            
            
            if ($msg->status_id === $statusSent->id) {
                // Проверяем есть ли подпись специалиста
                if ($specialist) {
                    $msg->status_id = $statusSignedBySpecialist->id;
                }
            }
            if ($msg->status_id === $statusSignedBySpecialist->id) {
                // Проверяем есть ли подпись руководителя ТФОМС
                if ($head) {
                    $msg->status_id = $statusSignedByHead->id;
                }
            }
            if ($msg->status_id === $statusSignedByHead->id) {
                // Проверяем есть ли подпись руководителя МО
                if ($mo) {
                    $msg->status_id = $statusReady->id;
                }
            }
            $msg->save();
        }
        
        if ($msg->type->name === 'mee') {
            $tfSpecialist = true;
            $tfHead = true;
            $moHead = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempTfSpecialist = false;
                $tempTfHead = false;
                $tempMoHead = false;
                
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-specialist mee')) {
                        $tempTfSpecialist = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider mee')) {
                        $tempTfHead = true;
                    }
                    if ($u->hasPermissionTo('sign-mo-lider mee')) {
                        $tempMoHead = true;
                    }
                }
                
                $tfSpecialist = $tfSpecialist && $tempTfSpecialist;
                $tfHead = $tfHead && $tempTfHead;
                $moHead = $moHead && $tempMoHead;
            }
            
            
            if ($msg->status_id === $statusSent->id) {
                // Проверяем есть ли подпись специалиста
                if ($tfSpecialist) {
                    $msg->status_id = $statusSignedBySpecialist->id;
                }
            }
            if ($msg->status_id === $statusSignedBySpecialist->id) {
                // Проверяем есть ли подпись руководителя ТФОМС
                if ($tfHead) {
                    $msg->status_id = $statusSignedByHead->id;
                }
            }
            if ($msg->status_id === $statusSignedByHead->id) {
                // Проверяем есть ли подпись руководителя МО
                if ($moHead) {
                    $msg->status_id = $statusReady->id;
                }
            }
            $msg->save();
        }
        
        if ($msg->type->name === 'reconciliation-act') {
            $tfSpecialist = true;
            $tfHead = true;
            $tfAccountant = true;
            $moHead = true;
            $moAccountant = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempTfSpecialist = false;
                $tempTfHead = false;
                $tempTfAccountant = false;
                $tempMoHead = false;
                $tempMoAccountant = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-specialist reconciliation-act')) {
                        $tempTfSpecialist = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider reconciliation-act')) {
                        $tempTfHead = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-accountant reconciliation-act')) {
                        $tempTfAccountant = true;
                    }
                    if ($u->hasPermissionTo('sign-mo-lider reconciliation-act')) {
                        $tempMoHead = true;
                    }
                    if ($u->hasPermissionTo('sign-mo-accountant reconciliation-act')) {
                        $tempMoAccountant = true;
                    }
                }
                $tfSpecialist = $tfSpecialist && $tempTfSpecialist;
                $tfHead = $tfHead && $tempTfHead;
                $tfAccountant = $tfAccountant && $tempTfAccountant;
                $moHead = $moHead && $tempMoHead;
                $moAccountant = $moAccountant && $tempMoAccountant;
            }
            // Проверяем есть ли подпись специалиста
            if ($tfSpecialist) {
                $msg->status_id = $statusSignedBySpecialist->id;
            }
            // Подписи специалиста И бухгалтера И руководителя ТФОМС => SignedByHead
            if ($tfSpecialist && $tfHead && $tfAccountant) {
                $msg->status_id = $statusSignedByHead->id;
            }
            // Для ФИН и BUCH
            $fin = [160, 161];
            $buch = [134, 171];
            if (in_array($msg->user_id, $fin) || in_array($msg->user_id, $buch)) {
                // Подписи специалиста И бухгалтера И руководителя ТФОМС => SignedByHead
                if ($tfHead && $tfAccountant) {
                    $msg->status_id = $statusSignedByHead->id;
                }
            }
            // Подписи бухгалтера И руководителя МО => Ready
            if ($msg->status_id === $statusSignedByHead->id) {
                if ($moHead && $moAccountant) {
                    $msg->status_id = $statusReady->id;
                }
            }
            $msg->save();
        }
        
        if ($msg->type->name === 'bill') {
            $accountant = true;
            $mo = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempMo = false;
                $tempAccountant = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider bill')) {
                        $tempMo = true;
                    }
                    if ($u->hasPermissionTo('sign-mo-accountant bill')) {
                        $tempAccountant = true;
                    }
                }
                $mo = $mo && $tempMo;
                $accountant = $accountant && $tempAccountant;
            }
            // Подписи бухгалтера ИЛИ руководителя => signing
            if ($accountant || $mo) {
                $msg->status_id = $statusSigning->id;
            }
            // Подписи бухгалтера И руководителя => SignedMo
            if ($accountant && $mo) {
                $msg->status_id = $statusSignedMo->id;
                // Для категории МТР => ready
                $msgCategories = $msg->category()->pluck('category_id')->toArray();
                if (in_array(3, $msgCategories)) {
                    $msg->status_id = $statusReady->id;
                }
            }
            $msg->save();
            
        }
        
        if ($msg->type->name === 'reg') {
            $statusRejectedFlc = MessageStatus::where('name','rejected_flc')->firstOrFail();
            $statusLoaded = MessageStatus::where('name','loaded')->firstOrFail();
            $statusInProgress = MessageStatus::where('name','in_progress')->firstOrFail();
            
            if (
                $msg->status_id === $statusRejectedFlc->id 
                || $msg->status_id === $statusLoaded->id
                || $msg->status_id === $statusInProgress->id
            ) {
                return;
            }
            
            $accountant = true;
            $mo = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempMo = false;
                $tempAccountant = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider reg')) {
                        $tempMo = true;
                    }
                    if ($u->hasPermissionTo('sign-mo-accountant reg')) {
                        $tempAccountant = true;
                    }
                }
                $mo = $mo && $tempMo;
                $accountant = $accountant && $tempAccountant;
            }
            // Подписи бухгалтера ИЛИ руководителя => signing
            if ($accountant || $mo) {
                $msg->status_id = $statusSigning->id;
            }
            // Подписи бухгалтера И руководителя => SignedMo
            if ($accountant && $mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            $msg->save();
            
        }
        
        if ($msg->type->name === 'agreement-fin') {
            
            $tf = true;
            $mo = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempTf = false;
                $tempMo = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider agreement-fin')) {
                        $tempMo = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider agreement-fin')) {
                        $tempTf = true;
                    }
                }
                $mo = $mo && $tempMo;
                $tf = $tf && $tempTf;
            }
            if ($tf) {
                $msg->status_id = $statusSignedByHead->id;
            }
            if ($mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            if ($tf && $mo) {
                $msg->status_id = $statusReady->id;
            }
            $msg->save();
            
        }
        
        if ($msg->type->name === 'agreement-fin-salaries') {
            
            $tf = true;
            $mo = true;
            $dzo = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempTf = false;
                $tempMo = false;
                $tampDzo = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider agreement-fin-salaries')) {
                        $tempMo = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider agreement-fin-salaries')) {
                        $tempTf = true;
                    }
                    if ($u->hasPermissionTo('sign-dzo-lider agreement-fin-salaries')) {
                        $tampDzo = true;
                    }
                }
                $mo = $mo && $tempMo;
                $tf = $tf && $tempTf;
                $dzo = $dzo && $tampDzo;
            }
            if ($tf) {
                $msg->status_id = $statusSignedByHead->id;
            }
            if ($tf && $mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            if ($tf && $mo && $dzo) {
                $msg->status_id = $statusReady->id;
            }
            $msg->save();
            
        }
        /**/
        if ($msg->type->name === 'contract-payment-oms') {
            $smoCount = 2;
            
            $tf = true;
            $mo = true;
            $smo = true;
            /*
            for ($i = 0; $i++; $i < $smoCount) {
                $s[i] = true;
            }
            */
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempTf = false;
                $tempMo = false;
                
                $smoSigned = [];
                
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider contract-payment-oms')) {
                        $tempMo = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider contract-payment-oms')) {
                        $tempTf = true;
                    }
                    // !! подписи СМО !!
                    if ($u->hasPermissionTo('sign-smo-lider contract-payment-oms')) {
                        $smoId = $u->organization->id;
                        if (!in_array($smoId, $smoSigned)) {
                            $smoSigned[] = $smoId;
                        }
                    }
                }
                $mo = $mo && $tempMo;
                $tf = $tf && $tempTf;
                if ( count($smoSigned) !== $smoCount) {
                    $smo = false;
                }
            }
            if ($tf) {
                $msg->status_id = $statusSignedByHead->id;
            }
            if ($mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            if ($tf && $mo && $smo) {
                $msg->status_id = $statusReady->id;
            }
            
            $msg->save();   
        }
        
        if ($msg->type->name === 'contract-financial-support-oms') {
            
            $tf = true;
            $smo = true;
            
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();
                
                $tempTf = false;
                $tempSmo = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-smo-lider contract-financial-support-oms')) {
                        $tempSmo = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider contract-financial-support-oms')) {
                        $tempTf = true;
                    }
                }
                $smo = $smo && $tempSmo;
                $tf = $tf && $tempTf;
            }
            if ($tf) {
                $msg->status_id = $statusSignedByHead->id;
            }
            /*
            if ($smo) {
                $msg->status_id = $statusSignedMo->id;
            }
            */
            if ($tf && $smo) {
                $msg->status_id = $statusReady->id;
            }
            $msg->save();
            
        }
    }
}
