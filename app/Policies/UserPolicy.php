<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

class UserPolicy {
    use HandlesAuthorization;

    public function assignRole(User $user, ?string $roleName)
    {
        $p = Permission::where('name','assign-role '.$roleName)->first();
        if($p !== null && $user->hasPermissionTo($p->id)){
            return true;
        }

        return false;
    }

    public function removeRole(User $user, ?string $roleName)
    {
        $p = Permission::where('name','remove-role '.$roleName)->first();
        if($p !== null && $user->hasPermissionTo($p->id)){
            return true;
        }

        return false;
    }
}
