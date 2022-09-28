<?php
declare(strict_types=1);

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

use App\Models\Organization;
use App\Models\Invite;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request, UserRoleService $userRoleService) {
        $validator = Validator::make($request->all(), [
            //'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'first_name' => 'required|string',
            'middle_name' => 'required|string',
            'last_name' => 'required|string',
            'snils' => 'required|string|min:11|max:11',
            'organization_id' => 'required|integer|exists:App\Models\Organization,id',
            'branch' => 'required|string',
            'job_title' => 'required|string',
            'invite' => 'required|string|max:10|exists:App\Models\Invite,invite'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Получаем из инвайта принудительно заданные свойства
        $inv = Invite::where('invite',$request->invite)->where('user_id',null)->firstOrFail();
        $org = [];
        // TODO: реализовать для всех заданных в options свойств
        if ($inv->options['organization_id']['forced']) {
            $org = [ 'organization_id' => $inv->options['organization_id']['value'] ];
        }

        $organization = Organization::find($request->organization_id);
        $organizationType = $organization->type->name;

        $fn = mb_substr($request->first_name, 0, 1);
        $mn = mb_substr($request->middle_name, 0, 1);
        $name = "{$organization->short_name} [{$request->last_name} {$fn}. {$mn}.]";

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password), 'name' => $name ],
                    $org
                ));

        $inv->user_id = $user->id;
        $inv->save();

        $userRoleService->assignRole($user->id, $organizationType, $user->id);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth()->user();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);
    }
}
