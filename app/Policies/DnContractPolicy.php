<?php

namespace App\Policies;

use App\Models\DnContract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DnContractPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, DnContract $dnContract)
    {
        if ($user->hasPermissionTo('receive all-dn-contract')
            || $user->hasPermissionTo('read all-dn-contract')
        ){
            return true;
        }
        if ($user->hasPermissionTo('receive mo-dn-contract')
            && $user->organization_id === $dnContract->mo_organization_id
        ) {
            return true;
        }
        return false;

    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, DnContract $dnContract)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, DnContract $dnContract)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, DnContract $dnContract)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, DnContract $dnContract)
    {
        return false;
    }
}
