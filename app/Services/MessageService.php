<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\Message;
use App\Models\MessageStatus;
use App\Models\MessageType;
use App\Models\Period;
use Illuminate\Http\Request;

use App\Http\Resources\MessageCollection;
use App\Http\Resources\MessageResource;

use App\Models\User;

use App\Jobs\CheckMessageStatus;

use Validator;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'type'      => 'array',
            'type.*'    => 'string|in:notype,bill,mek,mee,reconciliation-act,reg|distinct|exists:App\Models\MessageType,name',
            'status'   => 'array',
            'status.*'    => 'string|distinct|exists:App\Models\MessageStatus,name',
            'period'   => 'array',
            'period.*'    => 'integer|distinct|exists:App\Models\Period,id',
            'org' => 'array',
            'org.*'    => 'integer|distinct|exists:App\Models\Organization,id',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $perPage = (int)$request->input('per_page', 0);
        $msgIsIncoming = (bool)$request->input('in', false);
        if ($msgIsIncoming){
            $sql = $user->incomingMessages(); // Входящие
        } else {
            $sql = $user->outgoingMessages(); // Отправленные
        }
        // Статус
        $statuses = [];
        if (!empty($request->status)) {
            $statuses = MessageStatus::whereIn('name',$request->status)->pluck('id');
        } else {
            // по умолчанию
            $defaultStatuses = ['sent','signed_by_specialist','signed_by_head','ready','no_files','signed_mo','signing','rejected_flc','in_progress','loaded'];
            $statuses = MessageStatus::whereIn('name',$defaultStatuses)->pluck('id');
        }
        $sql = $sql->whereIn('status_id',$statuses);
        // Тип
        $types = [];
        if (!empty($request->type)) {
            $types = MessageType::whereIn('name',$request->type)->pluck('id');
        } else {
            // по умолчанию notype
            $types = MessageType::whereIn('name',['notype'])->pluck('id');
        }
        $sql = $sql->whereIn('type_id',$types);
        // Организация
        if (!empty($request->org)) {
            $sql = $sql->whereIn('organization_id',$request->org);
        }
        // Период
        if (!empty($request->period)) {
            $sql = $sql->whereIn('period_id',$request->period);
        }
        // Добавляем вложенные сущности User(от кого, кому), Category
        $sql = $sql->with(['to:id,name','from:id,name','category','status'])->OrderBy('created_at', 'desc');
        if($perPage == -1) {
            $result = $sql->paginate(999999999);
            return new MessageCollection($result);
        }

        return new MessageCollection($sql->paginate($perPage));
        // return $sql->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'text'    => 'nullable|string',
            'to'      => 'required|array|min:1',
            'to.*'    => 'integer|distinct|exists:App\Models\User,id',
            'attach'   => 'array',
            'attach.*' => 'integer|distinct|exists:App\Models\File,id',
            'category'   => 'array',
            'category.*' => 'integer|distinct|exists:App\Models\MessageCategory,id',
            'type' => 'string|distinct|exists:App\Models\MessageType,name',
            'period' => 'nullable|integer|exists:App\Models\Period,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $this->authorize('create', [Message::class, $request->type]);

        $msg = new Message();
        $msg->subject   = $request->subject;
        $msg->text      = $request->text;
        $msg->status_id = 2;
        $msg->user_id   = $userId;
        // Тип
        $type = null;
        if ($request->type) {
            $type = MessageType::where('name',$request->type)->first();
        } else {
            // по умолчанию notype
            $type = MessageType::where('name','notype')->first();
        }
        $msg->type_id   = $type->id;
        $msg->save();

        $msg->to()->attach($request->to);

        if (!empty($request->attach)) {
            $msg->files()->attach($request->attach);
        }

        if (!empty($request->category)) {
            $msg->category()->attach($request->category);
        }

        // TODO: сделать нормально
        // 67 - Баскова
        // 71 - Романенко
        // 72 - Хлыстова
        // 73 - Гончарова
        // 11 - Сахатский
        // 88 - Кобзарь
        // 89 - Симонова
        // 90 - Бурсина
        // 91 - Хохлачева
        // 85 - Березовская
        // 84 - Колташова
        // 86 - Шабалина
        // 281 - Сукманова
        $fin = [160, 161];
        $peo = [67, 71, 72, 73, 89];
        $mtr = [90, 91, 281];
        $omszpz = [85, 84, 86];
        $leadership = [11, 88];
        $accountant = [134];
        // Для счетов
        if ($request->type == 'bill') {
            //
            $attachUsersArr = [$msg->user_id];
            $org  = $user->organization;
            $msg->organization_id = $org->id;
            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих счета
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-accountant bill')
                    || $u->hasPermissionTo('sign-mo-lider bill')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            // Для категории Капитал
            if (in_array(1, $request->category)) {
                $attachUsersArr[] = 32;
            }
            // Для категории Астрамед
            if (in_array(2, $request->category)) {
                $attachUsersArr[] = 35;
                $attachUsersArr[] = 79;
            }

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $peo,
                $mtr,
                $leadership
            );
            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);

            // Период
            if (mb_strripos( $msg->subject, '01.2021')>-1) {
                $msg->period_id = 1;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '02.2021')>-1) {
                $msg->period_id = 2;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '03.2021')>-1) {
                $msg->period_id = 3;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '04.2021')>-1) {
                $msg->period_id = 4;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '05.2021')>-1) {
                $msg->period_id = 5;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '06.2021')>-1) {
                $msg->period_id = 9;
                $msg->save();
            }
        }
        // Для актов сверки
        if ($request->type == 'reconciliation-act') {
            $toUser = User::find($request->to[0]);
            $toOrg  = $toUser->organization;
            $msg->organization_id = $toOrg->id;
            $msg->subject   = $msg->subject . ' ' . $toOrg->short_name;
            $msg->save();

            $attachUsersArr = [$msg->user_id];


            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих акты
            // со стороны мед.организации
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-lider reconciliation-act')
                    || $u->hasPermissionTo('sign-mo-accountant reconciliation-act')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            // Для МТР
            if (in_array($msg->user_id, $mtr)) {
                $attachUsersArr = array_merge(
                    $attachUsersArr,
                    $mtr
                );
            }
            // Для ФИН
            if (in_array($msg->user_id, $fin)) {
                $attachUsersArr = array_merge(
                    $attachUsersArr,
                    $fin
                );
            }

            // со стороны ТФОМС
            $attachUsersArr = array_merge(
                $attachUsersArr,
                $leadership,
                $accountant
            );

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);

            // Период
            if (mb_strripos( $msg->subject, '01.2021')>-1) {
                $msg->period_id = 1;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '02.2021')>-1) {
                $msg->period_id = 2;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '03.2021')>-1) {
                $msg->period_id = 3;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '04.2021')>-1) {
                $msg->period_id = 4;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '05.2021')>-1) {
                $msg->period_id = 5;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '06.2021')>-1) {
                $msg->period_id = 9;
                $msg->save();
            }
        }

        // Для МЭК
        if ($request->type == 'mek') {
            $toUser = User::find($request->to[0]);
            $toOrg  = $toUser->organization;
            $msg->organization_id = $toOrg->id;
            $msg->subject   = $msg->subject . ' ' . $toOrg->short_name;

            if(isset($request->to[1])) {
                $toUser = User::find($request->to[1]);
                $toOrg  = $toUser->organization;
                $msg->subject   = $msg->subject . ' (' . $toOrg->short_name . ')';
            }
            $msg->save();

            $attachUsersArr = [];
            $attachUsersArr = array_merge(
                $attachUsersArr,
                $omszpz,
                $leadership
            );

            // Для ПЭО
            if (in_array($msg->user_id, $peo)) {
                $attachUsersArr = array_merge(
                    $attachUsersArr,
                    $peo
                );
            }
            // Для МТР
            if (in_array($msg->user_id, $mtr)) {
                $attachUsersArr = array_merge(
                    $attachUsersArr,
                    $mtr
                );
            }

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);

            // Период
            if (mb_strripos( $msg->subject, '01.2021')>-1) {
                $msg->period_id = 1;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '02.2021')>-1) {
                $msg->period_id = 2;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '03.2021')>-1) {
                $msg->period_id = 3;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '04.2021')>-1) {
                $msg->period_id = 4;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '05.2021')>-1) {
                $msg->period_id = 5;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '06.2021')>-1) {
                $msg->period_id = 9;
                $msg->save();
            }
        }
        // Для МЭЭ
        if ($request->type == 'mee') {
            $toUser = User::find($request->to[0]);
            $toOrg  = $toUser->organization;
            $msg->organization_id = $toOrg->id;
            $msg->subject   = $msg->subject . ' ' . $toOrg->short_name;
            $msg->save();

            $attachUsersArr = [$msg->user_id];
            $attachUsersArr = array_merge(
                $attachUsersArr,
                $omszpz,
                $leadership
            );

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);

            // Период
            if (mb_strripos( $msg->subject, '01.2021')>-1) {
                $msg->period_id = 1;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '02.2021')>-1) {
                $msg->period_id = 2;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '03.2021')>-1) {
                $msg->period_id = 3;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '04.2021')>-1) {
                $msg->period_id = 4;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '05.2021')>-1) {
                $msg->period_id = 5;
                $msg->save();
            }
            if (mb_strripos( $msg->subject, '06.2021')>-1) {
                $msg->period_id = 9;
                $msg->save();
            }
        }
        // Для РЕЕСТРОВ
        if ($request->type == 'reg') {
            // 152 - AIS
            $attachUsersArr = [$msg->user_id, 152];
            $org  = $user->organization;
            $msg->organization_id = $org->id;
            $msg->period_id = $request->period;
            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих счета
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-accountant reg')
                    || $u->hasPermissionTo('sign-mo-lider reg')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            // Для категории МТР
            if (in_array(3, $request->category)) {
                $attachUsersArr = array_merge(
                    $attachUsersArr,
                    $mtr
                );
            }

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $peo,
                //$mtr,
                $leadership
            );
            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }

        CheckMessageStatus::dispatch($msg);

        MessageResource::withoutWrapping();
        return new MessageResource($msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function show(int $msgId)
    {
        $user = Auth::user();
        $msg = $user->incomingMessages()->find($msgId); // Входящие
        if ($msg != null) {
            MessageResource::withoutWrapping();
            return new MessageResource($msg);
        }
        $msg = $user->outgoingMessages()->find($msgId); // Отправленные
        if ($msg != null) {
            MessageResource::withoutWrapping();
            return new MessageResource($msg);
        }
        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }
}
