<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    public function assignRole(UserRoleService $userRoleService, int $userId, string $roleName) {
        $user = Auth::user();

        $validator = Validator::make([
            'userId'   => $userId,
            'roleName' => $roleName,
        ], [
            'userId'   => 'integer|exists:App\Models\User,id',
            'roleName' => 'string|exists:Spatie\Permission\Models\Role,name',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $validated = $validator->validated();

        if($user->cannot('assignRole', [User::class, $validated['roleName']])) {
            return response()->json(['error' => 'Forbidden'], 403);
        };

        return $userRoleService->assignRole($validated['userId'], $validated['roleName'], $user->id);
    }

    public function removeRole(UserRoleService $userRoleService, int $userId, string $roleName) {
        $user = Auth::user();

        $validator = Validator::make([
            'userId'   => $userId,
            'roleName' => $roleName,
        ], [
            'userId'   => 'integer|exists:App\Models\User,id',
            'roleName' => 'string|exists:Spatie\Permission\Models\Role,name',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $validated = $validator->validated();

        if($user->cannot('removeRole', [User::class, $validated['roleName']])) {
            return response()->json(['error' => 'Forbidden'], 403);
        };

        return $userRoleService->removeRole($validated['userId'], $validated['roleName'], $user->id);
    }
}
