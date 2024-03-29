<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Message;
use App\Models\User;

class MessageForwardService {

    public function forwardOrganizationMessage(int $organizationId, array $msgTypeIdArr, array $attachToUsersIdArr, \DateTime $fromDateTime) {
        $msges = Message::where('organization_id', $organizationId)
                    ->whereIn('type_id', $msgTypeIdArr)
                    ->where('created_at','>',$fromDateTime)
                    ->get();
        foreach ($msges as $msg) {
            $msg->to()->syncWithoutDetaching($attachToUsersIdArr);
        }
    }

    public function forwardMessages(array $msgTypeIdArr, array $attachToUsersIdArr, \DateTime $fromDateTime) {
        $msgIds = Message::whereIn('type_id', $msgTypeIdArr)
                    ->where('created_at','>',$fromDateTime)
                    ->get()->pluck('id');
        foreach ($attachToUsersIdArr as $userId) {
            $user = User::find($userId);
            $user->incomingMessages()->syncWithoutDetaching($msgIds);
        }
    }

    /**
     * Переслать сообщения отправленные пользователем.
     *
     *
     *
     */
     /*
    function forwardMsg(int $msgSenderId, array $attachToUsersIdArr, array $msgTypeIdArr)
    {
        $msgIds = Message::where('user_id',$msgSenderId)
                    ->whereIn('type_id', $msgTypeIdArr)
                    ->get()->pluck('id');
        foreach ($attachToUsersIdArr as $userId) {
            $user = User::find($userId);
            $user->incomingMessages()->syncWithoutDetaching($msgIds);
        }
    }
    */

    /**
     * Переслать сообщения полученные пользователем.
     *
     *
     *
     */
    /**/
    function forwardInMessages(int $msgRecipientId, array $attachToUsersIdArr, array $msgTypeIdArr, \DateTime $fromDateTime, \DateTime $toDateTime)
    {
        $user = User::find($msgRecipientId);
        $msgIds = $user->incomingMessages()
                    ->whereIn('type_id', $msgTypeIdArr)
                    ->whereAnd('tbl_msg.created_at','>',$fromDateTime)
                    ->whereAnd('tbl_msg.created_at','<',$toDateTime)
                    ->get()
                    ->pluck('id');
        foreach ($attachToUsersIdArr as $userId) {
            $user = User::find($userId);
            $user->incomingMessages()->syncWithoutDetaching($msgIds);
        }
    }

}
