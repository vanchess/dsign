<?php

namespace App\Policies;

use App\Models\DnList;
use App\Models\DnListEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DnListEntriesPolicy
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
     * Проверяем возможность просмотра записи к листу
     *
     * @return
     */
    public function view(User $user, ?DnListEntry $entry, ?DnList $dnlist)
    {
        return in_array($user->id, $dnlist->message->to()->pluck('id')->toArray());
    }

    /**
     * Проверяем возможность добавления записи к листу
     *
     * @return
     */
    public function create(User $user, ?DnList $dnlist)
    {
        return $this->update($user, null, $dnlist);
    }

    /**
     * Проверяем возможность удаления записи из листа
     *
     * @return
     */
    public function delete(User $user, ?DnListEntry $entry, ?DnList $dnlist)
    {
        return $this->update($user, $entry, $dnlist);
    }

     /**
     * Проверяем возможность изменения записи к листу
     *
     * @return
     */
    public function update(User $user, ?DnListEntry $entry, ?DnList $displist)
    {
        $msg = $displist->message;

        if ($msg->organization->id !== $user->organization->id) {
            return false;
        }
        if ($msg->status->name !== 'draft') {
            return false;
        }

        if($user->hasPermissionTo('send dn-list')){
            return true;
        }

        return false;
    }
}
