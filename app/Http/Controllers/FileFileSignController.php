<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\File;
use App\Models\FileSign;
use Illuminate\Http\Request;

use App\Http\Resources\FileSignCollection;
use App\Http\Resources\FileSignResource;
use App\Jobs\ProcessSign;
use Validator;

class FileFileSignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(File $file)
    {
        return new FileSignCollection($file->signs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(File $file, Request $request)
    {
        $userId = Auth::id();
        
        $validator = Validator::make($request->all(), [
            'base64' => 'required|string',
        ]);
        
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $sign = new FileSign();
        $sign->user_id = $userId;
        $sign->file_id = $file->id;
        $sign->base64  = $request->base64;
        
        $sign->save();
        
        ProcessSign::dispatch($sign);
        
        FileSignResource::withoutWrapping();
        return new FileSignResource($sign);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
