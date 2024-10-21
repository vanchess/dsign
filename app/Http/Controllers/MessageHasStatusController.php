<?php

namespace App\Http\Controllers;

use App\Events\UserChangedMessageStatus;
use Illuminate\Support\Facades\Auth;

use App\Models\Message;
use App\Models\MessageStatus;
use Illuminate\Http\Request;

use App\Http\Resources\MessageStatusCollection;
use App\Http\Resources\MessageStatusResource;
use Validator;

class MessageHasStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Message $msg)
    {
        // return new MessageStatusResource($message->status);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Message $msg, Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|exists:App\Models\MessageStatus,name',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        if($request->status == 'rejected' &&
           $msg->type->name == 'bill' &&
           $user->hasPermissionTo('reject bill')
        ){
                $status = MessageStatus::where('name',$request->status)->firstOrFail();
                $msg->status_id = $status->id;
                $msg->save();
                return new MessageStatusResource($status);
        }
        if($request->status == 'sent-to-smo' &&
           $msg->type->name == 'bill' &&
           $msg->status->name == 'signed_mo' &&
           $user->hasPermissionTo('sent-to-smo bill')
        ){
                $status = MessageStatus::where('name',$request->status)->firstOrFail();
                $msg->status_id = $status->id;
                $msg->save();

                // TODO: Вынести отдельно
                $attachUsersArr = [];
                // Для категории Капитал
                $msgCategories = $msg->category()->pluck('category_id')->toArray();
                if (in_array(1, $msgCategories)) {
                    $attachUsersArr[] = 32;
                }
                // Для категории Астрамед
                if (in_array(2, $msgCategories)) {
                    $attachUsersArr[] = 35;
                    $attachUsersArr[] = 79;
                }
                $msg->to()->syncWithoutDetaching($attachUsersArr);

                return new MessageStatusResource($status);
        }

        if($request->status == 'rejected' &&
           $msg->type->name == 'mek' &&
           $user->hasPermissionTo('reject mek')
        ){
                $status = MessageStatus::where('name',$request->status)->firstOrFail();
                $msg->status_id = $status->id;
                $msg->save();
                return new MessageStatusResource($status);
        }

        if(
            ($request->status == 'rejected_flc' || $request->status == 'in_progress' || $request->status == 'loaded' /*|| $request->status == 'signed_mo'*/)
            && $msg->type->name == 'reg'
            && $user->hasPermissionTo('auto-set-status reg')
        ){
                $status = MessageStatus::where('name',$request->status)->firstOrFail();
                $msg->status_id = $status->id;
                $msg->save();
                return new MessageStatusResource($status);
        }

        if(
            ($request->status == 'rejected_flc' || $request->status == 'in_progress' || $request->status == 'loaded' /*|| $request->status == 'signed_mo'*/)
            && $msg->type->name == 'displist'
            && $user->hasPermissionTo('auto-set-status displist')
        ){
                $status = MessageStatus::where('name',$request->status)->firstOrFail();
                $msg->status_id = $status->id;
                $msg->save();
                return new MessageStatusResource($status);
        }

        if($request->status == 'sent' &&
           $msg->type->name == 'displist' &&
           $msg->status->name == 'draft' &&
           $user->hasPermissionTo('send displist') &&
           $msg->organization->id === $user->organization->id
        ){
            if ($msg->period->to < now() ) {
                // Срок отправки листа прошел -> отклоняем
                $statusRejectedFlc = MessageStatus::where('name','rejected_flc')->firstOrFail();
                $msg->status_id = $statusRejectedFlc->id;
                $msg->save();
            } else {
                $status = MessageStatus::where('name',$request->status)->firstOrFail();
                $msg->status_id = $status->id;
                $msg->created_at = now();
                $msg->save();

                // TODO: Вынести функционал изменения пользователем
                // статуса сообщения в отдельный сервис

                UserChangedMessageStatus::dispatch($msg->id, $msg->type->name, $request->status, $user->id);
                return new MessageStatusResource($status);
            }
        }

        if($request->status == 'sent' &&
           $msg->type->name == 'dn-list' &&
           $msg->status->name == 'draft' &&
           $user->hasPermissionTo('send dn-list') &&
           $msg->organization->id === $user->organization->id
        ){
            $status = MessageStatus::where('name',$request->status)->firstOrFail();
            $msg->status_id = $status->id;
            $msg->created_at = now();
            $msg->save();

            // TODO: Вынести функционал изменения пользователем
            // статуса сообщения в отдельный сервис

            UserChangedMessageStatus::dispatch($msg->id, $msg->type->name, $request->status, $user->id);
            return new MessageStatusResource($status);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
