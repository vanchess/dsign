<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Проверяем возможность отправки сообщения пользователем
     *
     * @return
     */
    public function create(User $user, ?string $messageType, ?int $periodId)
    {
        if($messageType === NULL){
            return true;
        }
        /**/
        if($messageType === 'reg') {
            if (time() > strtotime('2024-09-06 19:00')) { // KGN -5
                //if ($user->organization->id !== 9) {  // ГБУ "Курганская поликлиника №2"
                    return Response::deny('Прием реестров закрыт');
                //}
            }
        }

        if ($messageType === 'displist') {
            $period = Period::find($periodId);
            if ($period->to < now() ) {
                return false;
            }
        }


        if($user->hasPermissionTo('send '.$messageType)){
            return true;
        }

        return false;
    }
}
