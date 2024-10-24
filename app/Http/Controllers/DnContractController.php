<?php

namespace App\Http\Controllers;

use App\Http\Resources\DnContractResource;
use App\Models\DnContract;
use Illuminate\Http\Request;

class DnContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Http\Response
     */
    public function show(DnContract $dncontract)
    {
        // return DnContract::find($id);
        $this->authorize('view', [$dncontract]);

        return new DnContractResource($dncontract);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DnContract $dncontract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DnContract  $dnContract
     * @return \Illuminate\Http\Response
     */
    public function destroy(DnContract $dncontract)
    {
        //
    }
}
