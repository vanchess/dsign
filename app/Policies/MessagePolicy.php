<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
        if($user->hasPermissionTo('send '.$messageType)){
            return true;
        }

        return false;
    }
}
