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
use App\Models\Organization;

use App\Jobs\CheckMessageStatus;

use Validator;
use Illuminate\Validation\Rule;

function periodFromStr(string $s): int
{
    // 2022
    if (mb_strripos( $s, '10.2022')>-1) {
        return 25;
    }
    if (mb_strripos( $s, '09.2022')>-1) {
        return 24;
    }
    if (mb_strripos( $s, '08.2022')>-1) {
        return 23;
    }
    if (mb_strripos( $s, '07.2022')>-1) {
        return 22;
    }
    if (mb_strripos( $s, '06.2022')>-1) {
        return 21;
    }
    if (mb_strripos( $s, '05.2022')>-1) {
        return 20;
    }
    if (mb_strripos( $s, '04.2022')>-1) {
        return 19;
    }
    if (mb_strripos( $s, '03.2022')>-1) {
        return 18;
    }
    if (mb_strripos( $s, '02.2022')>-1) {
        return 17;
    }
    if (mb_strripos( $s, '01.2022')>-1) {
        return 16;
    }
    // 2021
    if (mb_strripos( $s, '01.2021')>-1) {
        return 1;
    }
    if (mb_strripos( $s, '02.2021')>-1) {
        return 2;
    }
    if (mb_strripos( $s, '03.2021')>-1) {
        return 3;
    }
    if (mb_strripos( $s, '04.2021')>-1) {
        return 4;
    }
    if (mb_strripos( $s, '05.2021')>-1) {
        return 5;
    }
    if (mb_strripos( $s, '06.2021')>-1) {
        return 9;
    }
    if (mb_strripos( $s, '07.2021')>-1) {
        return 10;
    }
    if (mb_strripos( $s, '08.2021')>-1) {
        return 11;
    }
    if (mb_strripos( $s, '09.2021')>-1) {
        return 12;
    }
    if (mb_strripos( $s, '10.2021')>-1) {
        return 13;
    }
    if (mb_strripos( $s, '11.2021')>-1) {
        return 14;
    }
    if (mb_strripos( $s, '12.2021')>-1) {
        return 15;
    }
    return -1;
}

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
            'type.*'    => 'string|in:notype,bill,mek,mee,reconciliation-act,reg,agreement-fin,contract-payment-oms,contract-financial-support-oms,agreement-fin-salaries|distinct|exists:App\Models\MessageType,name',
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
            $defaultStatuses = ['sent','signed_by_specialist','signed_by_head','ready','no_files','signed_mo','signing','rejected_flc','in_progress','loaded','sent-to-smo'];
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
            'toOrg'   => [
                Rule::requiredIf(function () use ($request) {
                    return $request->type === 'agreement-fin' || $request->type === 'contract-payment-oms';
                }),
                'array',
                'min:1'
            ],
            'toOrg.*'    => 'integer|distinct|exists:App\Models\Organization,id',
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
        // 92 - Завьялова
        $fin = [160, 161];
        $peo = [67, 71, 72, 73, 89];
        $mtr = [90, 91, 92];
        $buch = [134, 171];
        $omszpz = [85, 84, 86];
        $leadership = [11, 88];
        $myagkaya = [193];
        $accountant = [134];
        $lawyers = [189,190]; // Юристы
        // Страховые
        $astra = [35, 79];
        $kapital = [32];
        // Департамента здравоохранения
        $dzo = [196];
        // Для писем "почта" (тип не указан)
        if (!$request->type) {
            /* TODO:
                Временное решение.
                Все письма в разделе почта отправленные одному из абонентов отдела ПЭО
                дублируются для всего отдела (все сотрудники отдела добавляются в получатели)
            */
            $attachUsersArr = [];
            foreach ($request->to as $toUser) {
                if (in_array($toUser, $peo)) {
                    $attachUsersArr = array_merge(
                        $attachUsersArr,
                        $peo
                    );
                    break;
                }
            }
            if (count($attachUsersArr) > 0) {
                $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
                $msg->to()->syncWithoutDetaching($attachUsersArr);
            }
        }
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

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $peo,
                $mtr,
                $leadership
            );
            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);

            // Период
            $pId = periodFromStr($msg->subject);
            if ($pId > 0) {
                $msg->period_id = $pId;
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
            // Для БУХ
            if (in_array($msg->user_id, $buch)) {
                $attachUsersArr = array_merge(
                    $attachUsersArr,
                    $buch
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
            $pId = periodFromStr($msg->subject);
            if ($pId > 0) {
                $msg->period_id = $pId;
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
            $pId = periodFromStr($msg->subject);
            if ($pId > 0) {
                $msg->period_id = $pId;
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
                $leadership,
                $myagkaya
            );

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);

            // Период
            $pId = periodFromStr($msg->subject);
            if ($pId > 0) {
                $msg->period_id = $pId;
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
        // Для СОГЛАШЕНИЙ
        if ($request->type == 'agreement-fin') {
            $attachUsersArr = [$msg->user_id];
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->save();
            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих соглашения
            // со стороны мед.организации
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-lider agreement-fin')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }
            // Для ФИН и ЮР отделов и Руководства
            $attachUsersArr = array_merge($attachUsersArr,$fin,$lawyers,$leadership);

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для СОГЛАШЕНИЙ о софинансировании ЗП
        if ($request->type == 'agreement-fin-salaries') {
            $attachUsersArr = [$msg->user_id];
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->save();
            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих соглашения
            // со стороны мед.организации
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-lider agreement-fin-salaries')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }
            // Для ФИН и ЮР отделов и Руководства
            $attachUsersArr = array_merge($attachUsersArr,$fin,$lawyers,$leadership);
            // Для Департамента здравоохранения
            $attachUsersArr = array_merge($attachUsersArr,$dzo);

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для ДОГОВОРОВ НА ОПЛАТУ ПО ОМС
        if ($request->type == 'contract-payment-oms') {
            $attachUsersArr = [$msg->user_id];
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->save();
            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих договор
            // со стороны мед.организации
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-lider contract-payment-oms')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }
            // Для ЮР отдела, Руководства, Астрамед и Капитал
            $attachUsersArr = array_merge($attachUsersArr,$lawyers,$leadership,$astra,$kapital);

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для ДОГОВОРОВ О ФИНАНСОВОМ ОБЕСПЕЧЕНИИ ОМС
        if ($request->type == 'contract-financial-support-oms') {
            $attachUsersArr = [$msg->user_id];
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->save();
            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих договор
            // со стороны СМО
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-smo-lider contract-financial-support-oms')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }
            // Для ЮР отдела, Руководства
            $attachUsersArr = array_merge($attachUsersArr,$lawyers,$leadership);

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
