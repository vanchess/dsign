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
use App\Models\DispList;
use App\Models\DnContract;
use App\Models\DnList;
use Validator;
use Illuminate\Validation\Rule;

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
            'type.*'    => 'string|distinct|exists:App\Models\MessageType,name',
            'status'   => 'array',
            'status.*'    => 'string|distinct|exists:App\Models\MessageStatus,name',
            'period'   => 'array',
            'period.*'    => 'integer|distinct|exists:App\Models\Period,id',
            'org' => 'array',
            'org.*'    => 'integer|distinct|exists:App\Models\Organization,id',
            'category' => 'array',
            'category.*' => 'integer|distinct|exists:App\Model\MessageCategory,id'
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
            $defaultStatuses = ['draft','sent','signed_by_specialist','signed_by_head','ready','no_files','signed_mo','signing','rejected_flc','in_progress','loaded','sent-to-smo'];
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
        // Категории
        if (!empty($request->category)) {
            $sql = $sql->whereHas('category', function ($query) use ($request) {
                $query->whereIn('tbl_msg_category.id', $request->category);
            });
        }
        // Добавляем вложенные сущности User(от кого, кому), Category
        $withRelations = ['to:id,name','from:id,name','category','status'];
        if ($msgIsIncoming) {
            $withRelations = ['from:id,name','category','status'];
        }
        $sql = $sql->with($withRelations)->OrderBy('created_at', 'desc');
       // return $sql->toSql();
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
                    return $request->type === 'agreement-fin'
                    || $request->type === 'contract-payment-oms'
                    || $request->type === 'mek'
                    || $request->type === 'mee'
                    || $request->type === 'reconciliation-act'
                    || $request->type === 'mtr-refusal-reasons';
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
        $validated = $validator->validated();

        $this->authorize('create', [Message::class, $validated['type'] ?? null, $validated['period'] ?? null]);

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
        $fin = User::role('fin')->get()->pluck('id')->toArray();
        $peo = User::role('peo')->get()->pluck('id')->toArray();
        $mtr = User::role('mtr')->get()->pluck('id')->toArray();
        $buch = User::role('buch')->get()->pluck('id')->toArray();
        $omszpz = User::role('omszpz')->get()->pluck('id')->toArray();
        $leadership = User::role('leadership')->get()->pluck('id')->toArray();
        $tfDeputyDirectorOms = User::role('tf-deputy-director-oms')->get()->pluck('id')->toArray();
        $tfChiefAccountant = User::role('tf-chief-accountant')->get()->pluck('id')->toArray();
        $lawyers = User::role('lawyer')->get()->pluck('id')->toArray(); // Юристы
        // Страховые
        $astra = [35, 79];
        $kapital = [32];
        // Департамента здравоохранения
        $dzo = User::role('dzo')->get()->pluck('id')->toArray();
        // Для писем "почта" (тип не указан)
        if (!$request->type) {
            $attachUsersArr = [];
            // Все письма для ТФОМС в разделе почта дублируются на приемную
            // (Кроме писем отправленных сотрудниками ТФОМС)
            if (!$user->hasRole('tfoms'))
            {
                $tfoms = User::role('tfoms')->get()->pluck('id')->toArray();
                $receiveAllMailUsersIds = User::permission('receive all-mail-notype')->get()->pluck('id')->toArray();
                foreach ($request->to as $toUser) {
                    if (in_array($toUser, $tfoms)) {
                        $attachUsersArr = array_merge(
                            $attachUsersArr,
                            $receiveAllMailUsersIds
                        );
                        break;
                    }
                }
            }
            /* TODO:
                Временное решение.
                Все письма в разделе почта отправленные одному из абонентов отдела ПЭО
                дублируются для всего отдела (все сотрудники отдела добавляются в получатели)
            */
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
            $msg->period_id = $request->period;
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
                $leadership,
                $omszpz
            );
            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для актов сверки
        if ($request->type == 'reconciliation-act') {
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->subject   = $msg->subject . ' ' . $toOrg->short_name;
            $msg->period_id = $request->period;
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
                $tfChiefAccountant
            );

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }

        // Для МЭК
        if ($request->type == 'mek') {
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->period_id = $request->period;

            // Для категории Капитал
            if (in_array(1, $request->category)) {
                $msg->subject   = $msg->subject . ' («Капитал МС»)';
            }
            // Для категории Астрамед
            if (in_array(2, $request->category)) {
                $msg->subject   = $msg->subject . ' («АСТРАМЕД-МС»)';
            }
            $msg->save();

            $attachUsersArr = [$msg->user_id];

            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих МЭК
            // со стороны мед.организации
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-lider mek')
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
        }
        // Для МЭЭ
        if ($request->type == 'mee') {
            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->save();

            $attachUsersArr = [$msg->user_id];

            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей подписывающих МЭЭ
            // со стороны мед.организации
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('sign-mo-lider mee')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $omszpz,
                $leadership,
                $tfDeputyDirectorOms
            );

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для РЕЕСТРОВ
        if ($request->type == 'reg') {
            $attachUsersArr = [$msg->user_id];
            // Пересылаем все реестры на AIS
            $receiveAllRegUsersIds = User::permission('receive all-reg')->get()->pluck('id')->toArray();

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $receiveAllRegUsersIds
            );

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
        // Для Списков сотрудников на проф.мероприятия
        if ($request->type == 'displist') {
            $dl = new DispList();
            $dl->msg_id = $msg->id;
            $dl->save();
            $attachUsersArr = [$msg->user_id];
            // Пересылаем
            $receiveAllDispListUsersIds = User::permission('receive all-displist')->get()->pluck('id')->toArray();

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $receiveAllDispListUsersIds
            );

            $org  = $user->organization;
            $msg->status_id = 1; // черновик
            $msg->organization_id = $org->id;
            $msg->period_id = $request->period;
            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('receive mo-displist')
                    || $u->hasPermissionTo('sign-mo-lider displist')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для договоров диспансерного наблюдения
        if ($request->type == 'dn-contract') {
            // Пересылаем
            $attachUsersArr = array_merge(
                [$msg->user_id],
                User::permission('receive all-dn-contract')->get()->pluck('id')->toArray(),
                User::permission('confirm dn-contract')->get()->pluck('id')->toArray()
            );

            $org  = $user->organization;
           // $msg->status_id = 1; // черновик
            $msg->organization_id = $org->id;
           // $msg->period_id = $request->period;
            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('receive mo-dn-contract')
                    || $u->hasPermissionTo('sign-mo-lider dn-contract')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для Списков сотрудников на диспансерное наблюдение
        if ($request->type == 'dn-list') {
            $org  = $user->organization;

            $dl = new DnList();
            $dl->msg_id = $msg->id;
            $date = date('Y-m-d H:i:s');
            $dnContract = DnContract::where('ogrn',$msg->text)
                            ->where('mo_organization_id',$org->id)
                            ->WhereRaw("? BETWEEN effective_from AND effective_to", [$date])
                            ->firstOrFail();
            $dl->contract_id = $dnContract->id;

            $dl->save();

            $attachUsersArr = [$msg->user_id];
            // Пересылаем
            $receiveAllDnListUsersIds = User::permission('receive all-dn-list')->get()->pluck('id')->toArray();

            $attachUsersArr = array_merge(
                $attachUsersArr,
                $receiveAllDnListUsersIds
            );

            $msg->status_id = 1; // черновик
            $msg->organization_id = $org->id;

            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('receive mo-dn-list')
                    || $u->hasPermissionTo('sign-mo-lider dn-list')
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }

        // Для заявок СМО на аванс
        if ($request->type == 'smo-fin-advance') {
            $mType = 'smo-fin-advance';
            // Пересылаем
            $attachUsersArr = array_merge(
                [$msg->user_id],
                User::permission('receive all-'.$mType)->get()->pluck('id')->toArray(),
            );

            $org  = $user->organization;
            $msg->subject   = $msg->subject . ' ' . $org->short_name;
            $msg->organization_id = $org->id;
            $msg->period_id = $request->period;
            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей
            // со стороны СМО
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('receive smo-' . $mType)
                    || $u->hasPermissionTo('sign-smo-lider ' . $mType)
                    || $u->hasPermissionTo('sign-smo-accountant ' . $mType)
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для заявок СМО на расчет
        if ($request->type == 'smo-fin-payment') {
            $mType = 'smo-fin-payment';
            // Пересылаем
            $attachUsersArr = array_merge(
                [$msg->user_id],
                User::permission('receive all-'.$mType)->get()->pluck('id')->toArray(),
            );

            $org  = $user->organization;
            $msg->subject   = $msg->subject . ' ' . $org->short_name;
            $msg->organization_id = $org->id;
            $msg->period_id = $request->period;
            $msg->save();
            $orgUsers = $org->users()->with('permissions')->get();
            // Добавляем пользователей
            // со стороны СМО
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('receive smo-' . $mType)
                    || $u->hasPermissionTo('sign-smo-lider ' . $mType)
                    || $u->hasPermissionTo('sign-smo-accountant ' . $mType)
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

            $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
            $msg->to()->syncWithoutDetaching($attachUsersArr);
        }
        // Для Ведомость причин отказа
        if ($request->type == 'mtr-refusal-reasons') {
            $mType = 'mtr-refusal-reasons';

            $orgId = $request->toOrg[0];
            $toOrg  = Organization::find($orgId);
            $msg->organization_id = $toOrg->id;
            $msg->subject   = $msg->subject . ' ' . $toOrg->short_name;
            $msg->period_id = $request->period;
            $msg->save();

            // Пересылаем
            $attachUsersArr = array_merge(
                [$msg->user_id],
                User::permission('receive all-'.$mType)->get()->pluck('id')->toArray(),
                User::permission('sign-tf-lider '.$mType)->get()->pluck('id')->toArray(),
                User::permission('sign-specialist '.$mType)->get()->pluck('id')->toArray(),
            );

            
            $orgUsers = $toOrg->users()->with('permissions')->get();
            // Добавляем пользователей
            // со стороны МО
            foreach ($orgUsers as $u) {
                if (
                    $u->hasPermissionTo('receive mo-' . $mType)
                ) {
                    $attachUsersArr[] = $u->id;
                }
            }

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
