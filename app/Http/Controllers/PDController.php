<?php

namespace App\Http\Controllers;

use App\Models\PD;
use Illuminate\Http\Request;
use Validator;

class PDController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PD  $pD
     * @return \Illuminate\Http\Response
     */
    public function show(string $invite)
    {
        $pd = PD::where('invite',$invite)->firstOrFail();
        return $pd;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PD  $pD
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $invite)
    {
        $validator = Validator::make($request->all(), [
            'last_name'   => 'required|string',
            'first_name'  => 'required|string',
            'middle_name' => 'required|string',
            'address' => 'required|string',
            'snils' => 'required|string',
            'p_series' => 'required|string',
            'p_number' => 'required|string',
            'p_issued_by' => 'required|string',
            'p_department_code' => 'required|string',
            'p_date' => 'required|date',
            'birthday' => 'required|date',
            'place_of_birth' => 'required|string',
            'inn' => 'required|string',
        ]);

        $pd = PD::where('invite',$invite)->firstOrFail();
        $pd->last_name = $request->last_name;
        $pd->first_name = $request->first_name;
        $pd->middle_name = $request->middle_name;
        $pd->address = $request->address;
        $pd->snils = $request->snils;
        $pd->p_series = $request->p_series;
        $pd->p_number = $request->p_number;
        $pd->p_issued_by = $request->p_issued_by;
        $pd->p_department_code = $request->p_department_code;
        $pd->p_date = $request->p_date;
        $pd->birthday = $request->birthday;
        $pd->place_of_birth = $request->place_of_birth;
        $pd->inn = $request->inn;
        $pd->save();
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PD  $pD
     * @return \Illuminate\Http\Response
     */
    public function destroy(PD $pD)
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
