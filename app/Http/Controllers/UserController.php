<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController  extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'int',
            'include' => ['string', Rule::in(['roles'])],
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $validated = $validator->validated();

        $perPage = (int)$validated['per_page'] ?? 0;

        $sql = User::OrderBy('name');
        if (isset($validated['include'])) {
            $sql = $sql->with([$validated['include']]);
        }
        if($perPage == -1) {
            $result = $sql->paginate(999999999);
            return new UserCollection($result);
        }
        return new UserCollection($sql->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
