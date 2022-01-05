<?php

namespace App\Http\Controllers;

use App\Models\MessageStatus;
use Illuminate\Http\Request;

use App\Http\Resources\MessageStatusCollection;
use App\Http\Resources\MessageStatusResource;

class MessageStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = (int)$request->input('per_page', 0);
        
        $sql = MessageStatus::OrderBy('name');
        if($perPage == -1) {
            $result = $sql->paginate(999999999);
            return new MessageStatusCollection($result);
        }
        return new MessageStatusCollection($sql->paginate($perPage));
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
     * @param  \App\Models\MessageStatus  $messageStatus
     * @return \Illuminate\Http\Response
     */
    public function show(MessageStatus $msg_status)
    {
        MessageStatusResource::withoutWrapping();
        return new MessageStatusResource($msg_status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MessageStatus  $messageStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MessageStatus $messageStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MessageStatus  $messageStatus
     * @return \Illuminate\Http\Response
     */
    public function destroy(MessageStatus $messageStatus)
    {
        //
    }
}
