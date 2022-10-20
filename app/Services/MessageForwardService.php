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
    function forwardMsg(int $msgSenderId, array $attachToUsersIdArr, int $msgTypeId)
    {
        $msges = Message::where('user_id',$msgSenderId)
                    ->where('type_id', $msgTypeId)
                    ->get();
        foreach ($msges as $msg) {
            $msg->to()->syncWithoutDetaching($attachToUsersIdArr);
        }
    }
    */

    /**
     * Переслать сообщения полученные пользователем.
     *
     *
     *
     */
    /*
    function forwardInMsg(int $msgRecipientId, array $attachToUsersIdArr, int $msgTypeId)
    {
        $user = User::find($msgRecipientId);
        $msges = $user->incomingMessages()->where('type_id',$msgTypeId)->get();
        foreach ($msges as $msg) {

            $msg->to()->syncWithoutDetaching($attachToUsersIdArr);
        }
    }
    */
}
