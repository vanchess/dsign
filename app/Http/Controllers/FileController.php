<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\File;
use Illuminate\Http\Request;

use App\Http\Resources\FileCollection;
use App\Http\Resources\FileResource;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        
        $perPage = (int)$request->input('per_page', 0);
        
        $sql = File::Where('user_id',$userId)->OrderBy('created_at');
        if($perPage == -1) {
            $result = $sql->paginate(999999999);
            return new FileCollection($result);
        }
        return new FileCollection($sql->paginate($perPage));
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
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
