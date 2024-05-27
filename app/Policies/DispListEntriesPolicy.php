<?php

namespace App\Policies;

use App\Models\DispList;
use App\Models\DispListEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DispListEntriesPolicy
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
     * Проверяем возможность добавления записи к листу
     *
     * @return
     */
    public function view(User $user, ?DispListEntry $entry, ?DispList $displist)
    {
        return in_array($user->id, $displist->message->to()->pluck('id')->toArray());
    }

    /**
     * Проверяем возможность добавления записи к листу
     *
     * @return
     */
    public function create(User $user, ?DispList $displist)
    {
        return $this->update($user, null, $displist);
    }

    /**
     * Проверяем возможность удаления записи из листа
     *
     * @return
     */
    public function delete(User $user, ?DispListEntry $entry, ?DispList $displist)
    {
        return $this->update($user, $entry, $displist);
    }

     /**
     * Проверяем возможность изменения записи к листу
     *
     * @return
     */
    public function update(User $user, ?DispListEntry $entry, ?DispList $displist)
    {
        if ($displist->message->organization->id !== $user->organization->id) {
            return false;
        }

        if($user->hasPermissionTo('send displist')){
            return true;
        }

        return false;
    }
}
