<?php

namespace App\Jobs;

use App\Events\MessageStatusChecked;
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
        $oldStatusId = $msg->status_id;

        $statusDraft = MessageStatus::where('name','draft')->firstOrFail();
        $statusReady = MessageStatus::where('name','ready')->firstOrFail();
        $statusRejected = MessageStatus::where('name','rejected')->firstOrFail();
        if (
            $msg->status_id === $statusReady->id ||
            $msg->status_id === $statusRejected->id ||
            $msg->status_id === $statusDraft->id
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
            $tfSpecialist = true;
            $tfHead = true;
            $moHead = true;

            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $tempTfSpecialist = false;
                $tempTfHead = false;
                $tempMoHead = false;

                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-specialist mek')) {
                        $tempTfSpecialist = true;
                    }
                    if ($u->hasPermissionTo('sign-tf-lider mek')) {
                        $tempTfHead = true;
                    }
                    if ($u->hasPermissionTo('sign-mo-lider mek')) {
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
                
                    // Для категории Протокол => SignedByHead (подписано ТФ ОМС)
                    $msgCategories = $msg->category()->pluck('category_id')->toArray();
                    if (in_array(16, $msgCategories)) {
                        $msg->status_id = $statusSignedByHead->id;
                    }
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
            $tfHead = true;
            $tfAccountant = true;
            $moHead = true;
            $moAccountant = true;

            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $tempTfHead = false;
                $tempTfAccountant = false;
                $tempMoHead = false;
                $tempMoAccountant = false;
                foreach ($signUsers as $u) {
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
                $tfHead = $tfHead && $tempTfHead;
                $tfAccountant = $tfAccountant && $tempTfAccountant;
                $moHead = $moHead && $tempMoHead;
                $moAccountant = $moAccountant && $tempMoAccountant;
            }
            // бухгалтера И руководителя ТФОМС => SignedByHead
            if ($tfHead && $tfAccountant) {
                $msg->status_id = $statusSignedByHead->id;
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

        if ($msg->type->name === 'displist') {
            $statusRejectedFlc = MessageStatus::where('name','rejected_flc')->firstOrFail();
            $statusLoaded = MessageStatus::where('name','loaded')->firstOrFail();
            $statusInProgress = MessageStatus::where('name','in_progress')->firstOrFail();

            if (
                $msg->status_id === $statusRejectedFlc->id
                || $msg->status_id === $statusLoaded->id
                || $msg->status_id === $statusInProgress->id
                || $msg->status_id === $statusSignedMo->id
            ) {
                return;
            }

            if ($msg->period->to < now()->subMonth() ) {
                // Срок отправки листа прошел -> отклоняем
                $msg->status_id = $statusRejectedFlc->id;
                $msg->save();
                return;
            }

            $mo = true;
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $tempMo = false;
                foreach ($signUsers as $u) {
                    if ($u->hasPermissionTo('sign-mo-lider displist')) {
                        $tempMo = true;
                    }
                }
                $mo = $mo && $tempMo;
            }
            // Подписи руководителя => SignedMo
            if ($mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            $msg->save();
        }

        if ($msg->type->name === 'dn-contract') {

            $mo = true;
            $tf = true;
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $mo = $mo && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-mo-lider dn-contract');
                });
                $tf = $tf && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('confirm dn-contract');
                });
            }
            // Подписи руководителя => SignedMo
            if ($mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            if ($mo && $tf) {
                $msg->status_id = $statusReady->id;
            }
            $msg->save();
        }

        if ($msg->type->name === 'dn-list') {
            $statusRejectedFlc = MessageStatus::where('name','rejected_flc')->firstOrFail();
            $statusLoaded = MessageStatus::where('name','loaded')->firstOrFail();
            $statusInProgress = MessageStatus::where('name','in_progress')->firstOrFail();

            if (
                $msg->status_id === $statusRejectedFlc->id
                || $msg->status_id === $statusLoaded->id
                || $msg->status_id === $statusInProgress->id
                || $msg->status_id === $statusSignedMo->id
            ) {
                return;
            }

            $mo = true;
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $mo = $mo && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-mo-lider dn-list');
                });
            }
            // Подписи руководителя => SignedMo
            if ($mo) {
                $msg->status_id = $statusSignedMo->id;
            }
            $msg->save();
        }
        if ($msg->type->name === 'smo-fin-advance') {

            $smoLider = true;
            $smoAccountant = true;
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $smoAccountant = $smoAccountant && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-smo-accountant smo-fin-advance');
                });
                $smoLider = $smoLider && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-smo-lider smo-fin-advance');
                });
            }
            // 
            if ($smoAccountant) {
                $msg->status_id = $statusSigning->id;
            }
            if ($smoLider) {
                $msg->status_id = $statusSigning->id;
            }
            if ($smoAccountant && $smoLider) {
                $msg->status_id = $statusReady->id;
            }
            $msg->save();
        }
        if ($msg->type->name === 'smo-fin-payment') {

            $smoLider = true;
            $smoAccountant = true;
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $smoAccountant = $smoAccountant && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-smo-accountant smo-fin-payment');
                });
                $smoLider = $smoLider && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-smo-lider smo-fin-payment');
                });
            }
            // 
            if ($smoAccountant) {
                $msg->status_id = $statusSigning->id;
            }
            if ($smoLider) {
                $msg->status_id = $statusSigning->id;
            }
            if ($smoAccountant && $smoLider) {
                $msg->status_id = $statusReady->id;
            }
            $msg->save();
        }
        if ($msg->type->name === 'mtr-refusal-reasons') {
            $tfSpecialist = true;
            $tfHead = true;
            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $tfSpecialist = $tfSpecialist && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-specialist mtr-refusal-reasons');
                });
                $tfHead = $tfHead && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-tf-lider mtr-refusal-reasons');
                });
            }
            // 
            if ($tfSpecialist && $tfHead) {
                $msg->status_id = $statusReady->id;
            } else {
                if ($tfSpecialist) {
                    $msg->status_id = $statusSignedBySpecialist->id;
                }
                if ($tfHead) {
                    $msg->status_id = $statusSigning->id;
                }
            }
            
            $msg->save();
        }
        if ($msg->type->name === 'rmee') {
            $tfFinSpecialist = true;
            $tfOmsZpzSpecialist = true;
            $tfHead  = true;
            $moHead  = true;
            $smoHead = true;

            foreach ($files as $f) {
                $signUsers = $f->signUsers()->where('verified_on_server_success',true)->distinct()->get();

                $tfFinSpecialist = $tfFinSpecialist && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-tf-fin-spec rmee');
                });
                $tfOmsZpzSpecialist = $tfOmsZpzSpecialist && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-tf-omszpz-spec rmee');
                });
                $tfHead = $tfHead && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-tf-lider rmee');
                });
                $moHead = $moHead && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-mo-lider rmee');
                });
                $smoHead = $smoHead && $signUsers->contains(function ($user) {
                    return $user->hasPermissionTo('sign-smo-lider rmee');
                });

            }
            if ($tfFinSpecialist && $tfOmsZpzSpecialist && $tfHead && $moHead && $smoHead) {
                $msg->status_id = $statusReady->id;
            } else {
                if ($tfFinSpecialist || $tfOmsZpzSpecialist || $tfHead || $moHead || $smoHead) {
                    $msg->status_id = $statusSigning->id;
                }
                if ($tfFinSpecialist && $tfOmsZpzSpecialist) {
                    $msg->status_id = $statusSignedBySpecialist->id;
                }
                if ($tfFinSpecialist && $tfOmsZpzSpecialist && $tfHead) {
                    $msg->status_id = $statusSignedByHead->id;
                }
                if ($tfFinSpecialist && $tfOmsZpzSpecialist && $tfHead && $moHead) {
                    $msg->status_id = $statusSignedMo->id;
                }

                // Для категории Протокол => SignedByHead (подписано ТФ ОМС)
                $msgCategories = $msg->category()->pluck('category_id')->toArray();
                if (in_array(16, $msgCategories)) {
                    if ($moHead && $tfOmsZpzSpecialist) {
                        $msg->status_id = $statusReady->id;
                    }
                }
            }
            
            $msg->save();
        }


        MessageStatusChecked::dispatch(
            $msg->id,
            $msg->type->name,
            MessageStatus::findOrFail($oldStatusId)->name,
            MessageStatus::findOrFail($msg->status_id)->name
        );
    }
}
