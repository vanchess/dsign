<?php
declare(strict_types=1);

namespace App\Services;

use App\Events\UserRoleAdd;
use App\Models\User;

class UserRoleService {

    public function assignRole(int $userId, string $roleName, int $assignedByUserId) {
        $user = User::find($userId);
        $user->assignRole($roleName);
        UserRoleAdd::dispatch($userId, $roleName, $assignedByUserId);
        return true;
    }

    public function removeRole(int $userId, string $roleName, int $assignedByUserId) {
        $user = User::find($userId);
        return $user->removeRole($roleName);
    }
}
