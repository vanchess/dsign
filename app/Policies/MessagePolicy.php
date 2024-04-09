<?php

namespace App\Policies;

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
    public function create(User $user, ?string $messageType)
    {
        if($messageType === NULL){
            return true;
        }
        /**/
        if($messageType === 'reg') {
            if (time() > strtotime('2024-04-09 19:00')) { // KGN -5
                //if ($user->organization->id !== 9) {  // ГБУ "Курганская поликлиника №2"
                    return Response::deny('Прием реестров закрыт');
                //}
            }
        }
        
        
        if($user->hasPermissionTo('send '.$messageType)){
            return true;
        }

        return false;
    }
}
