<?php

use Illuminate\Support\Facades\Route;

use App\Jobs\ProcessSign;
use App\Models\FileSign;

use App\Models\CryptoCert;
use App\Jobs\CreateSignStamp;
use App\Jobs\CheckMessageStatus;

use App\Models\File;
use App\Models\Organization;
use App\Models\FileSignStamp;


use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Models\User;
use App\Models\Message;
use App\Models\MessageStatus;
use App\Models\Period;
use App\Models\PD;

use Illuminate\Support\Facades\DB;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Переслать сообщения отправленные пользователем.
 *
 * 
 * 
 */
function forwardMsg($msgSenderId, $attachToUsersIdArr, $msgTypeId)
{
    $msges = Message::where('user_id',$msgSenderId)->where('type_id', $msgTypeId)->get();
    foreach ($msges as $msg) {
        $msg->to()->syncWithoutDetaching($attachToUsersIdArr);
    }
}

/**
 * Переслать сообщения полученные пользователем.
 *
 * 
 * 
 */
function forwardInMsg($msgRecipientId, $attachToUsersIdArr, $msgTypeId)
{
    $user = User::find($msgRecipientId);
    $msges = $user->incomingMessages()->where('type_id',$msgTypeId)->get();
    foreach ($msges as $msg) {

        $msg->to()->syncWithoutDetaching($attachToUsersIdArr);
    }
}

Route::get('/', function () {
    
    //phpinfo();
    //CreateSignStamp::dispatch(File::find(26094));
    //$certs = File::find(19684)->signCerts()->distinct()->get();
    //return $certs;
   /* 
    $permission = Permission::create(['name' => 'monitor reg']);
    $role = Role::where('name', 'mo-lider')->first();
    $role->givePermissionTo($permission);
  */ 
  /*
    $permission = Permission::where('name', 'monitor reg')->first();
    $role = Role::where('name', 'tfoms')->first();
    //$role = Role::create(['name' => 'tfoms']);
    $role->givePermissionTo($permission);
  */
  
    //$permission->delete();
   
    
   /*
    // $signs = FileSign::All();
    // $signs = FileSign::whereIn('id',[41258,41257,41256,41255,41254,41037,41036,41035,34810,34574,34573,34572,34178,34177,34176,28095,28089])->get();
    // $signs = FileSign::where('user_id',158)->get();
    $signs = FileSign::where('id','>',63959)->where('id','<',64012)->get();
    // return $sign;
    foreach($signs as $sign)
    {
        echo $sign->id;
        ProcessSign::dispatch($sign);
    }
    */
/*
    $role = Role::create(['name' => 'buch']);
    $permission = Permission::where('name', 'send reconciliation-act')->first();
    $role->givePermissionTo($permission);
    */
    //$user = User::find(35);
    //$user->removeRole('mo-lider');
    //$user->assignRole('smo-lider');
    //$user = User::find(25);
    //$user->removeRole('mo-lider');
    //$user->assignRole('smo-lider');
    //$user->assignRole('tfoms');
    
    
    

    /*
    $messenges = Message::withTrashed()->where('type_id',5)->get();
    foreach ($messenges as $msg) {
        $toUsers = $msg->to;
        $user = null;
        foreach ($toUsers as $u) {
            if ($u->hasRole('mo-lider')) {
                 $user = $u;
            }
        }
        
        echo $msg->id;
        echo ': ';
        echo $msg->subject;
        echo ' : ';
        
        if (!$user) {
            echo '!!!!!!!!!!!!!!';
            continue;
        }
        
        $org  = $user->organization;
    
        echo $org->name;
        echo ' : ';
        echo $user->name;
        echo '<br>';
        $msg->organization_id = $org->id;
        $msg->save();
    }
    return 'ок';
    */
    
    /*

    $msgs = Message::whereIn('id',[16675,16672,16671])->get();
    foreach ($msgs as $msg) {
        CheckMessageStatus::dispatch($msg);
    }
    return 'ok';  
    */

    
    


//$users = PD::all();
//return $users;
/*  
    $monthNum = '12';
    //$tz = new DateTimeZone( '+0500' );
    $month = new DateTime("2021-${monthNum}-01T00:00:00.000000+0500");
    $from = new DateTime("first day of {$month->format('F')} 2021+0500");
    $to = new DateTime("last day of {$month->format('F')} 2021T23:59:59.999999+0500");
    $from->setTimezone(new DateTimeZone('UTC'));
    $to->setTimezone(new DateTimeZone('UTC'));
    Period::firstOrCreate(['from' => $from, 'to' => $to]);
    
    return $period;
  */
    
    /*
    $messenges = Message::withTrashed()->where('type_id',2)->get();
    foreach ($messenges as $msg) {
        $user = User::find($msg->user_id);
        $org  = $user->organization;
        echo $msg->id;
        echo ': ';
        echo $msg->subject;
        echo ' : ';
        echo $org->name;
        echo ' : ';
        echo $user->name;
        echo '<br>';
        $msg->organization_id = $org->id;
        $msg->save();
    }
    return 'ок';
    */
    
    /*
    // Определяем период по теме
    
    foreach ($messenges as $msg) {
        if (
            mb_strripos( $msg->subject, '01.2021') > -1 ||
            mb_strripos( $msg->subject, 'январь') > -1
        ) {
            echo 'январь';
            echo '    ';
            $msg->period_id = 1;
            $msg->save();
        }
        echo $msg->id;
        echo ': ';
        echo $msg->subject;
        echo '<br>';
       
    }
    return 'ok';
    */
    
    /*
    // Удалить письма (3 - МЭКи) пользователя 
    $user = User::find(89);
    $messenges = $user->outgoingMessages()->where('type_id',3)->get();
    foreach ($messenges as $msg) {
        echo $msg->user_id;
        echo '   ';
        echo $msg->subject;
        echo '<br>';
        $msg->delete();
    }
    
    return 'ok';
    */
/*   
    // Регистрация руководителя МО
    $userHeadId = 191;
    $user = User::find($userHeadId);
    $user->assignRole('mo');
    $user->assignRole('mo-lider');

    // Переслать cчета отправленные пользователем
    //forwardMsg(25, [$userHeadId], 2);
    // Пересылаем cчета полученые пользователем
    forwardInMsg(25, [$userHeadId], 2);
    // Пересылаем акты сверки полученые пользователем
    forwardInMsg(25, [$userHeadId], 5);
    // Пересылаем акты МЭК полученые пользователем
    forwardInMsg(25, [$userHeadId], 3);
    // Пересылаем акты МЭЭ полученые пользователем
    forwardInMsg(25, [$userHeadId], 4);
    // Переслать реестры отправленные пользователем
    //forwardMsg(25, [$userHeadId], 6);
    // Пересылаем cчета полученые пользователем
    forwardInMsg(25, [$userHeadId], 6);
    // Пересылаем соглашения(фин) полученые пользователем
    forwardInMsg(25, [$userHeadId], 7);
    // Пересылаем договоры на оказание и оплату МП по ОМС полученые пользователем
    forwardInMsg(25, [$userHeadId], 8);
    
    // Переслать cчета отправленные пользователем
    //forwardMsg(126, [$userHeadId], 2);
    // Переслать реестры отправленные пользователем
    //forwardMsg(126, [$userHeadId], 6);
*//*

 
    // Регистрация главного бухгалтера МО
    $userHeadId = 75;
    $userAccountantId = 157; 
    $user = User::find($userAccountantId);
    $user->assignRole('mo');
    $user->assignRole('mo-chief-accountant'); 
  
    // Переслать cчета отправленные пользователем
    forwardMsg($userHeadId, [$userAccountantId], 2);
    // Пересылаем акты сверки полученые пользователем
    forwardInMsg($userHeadId, [$userAccountantId], 5);
    // Переслать реестры отправленные пользователем
    forwardMsg($userHeadId, [$userAccountantId], 6);
    
    $oldAccountantId = 184;
    // Переслать cчета отправленные пользователем
    forwardMsg($oldAccountantId, [$userAccountantId], 2);
    // Пересылаем акты сверки полученые пользователем
    forwardInMsg($oldAccountantId, [$userAccountantId], 5);
    // Переслать реестры отправленные пользователем
    forwardMsg($oldAccountantId, [$userAccountantId], 6);
    
    // Убрать роль главного бухгалтера у предыдущего главбуха
    $oldAccountant = User::find($oldAccountantId);
    $oldAccountant->removeRole('mo-chief-accountant');
    return 'ok';   
*/      
    
    
    return 'ok';
    
    //$messenges = Message::All();
    $messenges = Message::where('type_id',5)->get();
    
    foreach ($messenges as $msg) {
        $toUsers = $msg->to;
        $files = $msg->files;
        
        foreach ($toUsers as $u) {
            echo $u->name;
            echo '<br>';
        }
        foreach ($files as $f) {
            echo $f->name;
            echo '<br>';
        }
    }
    
    return 'ok'; 
    
    
    /*
    // Перенос из МЭКов в Акты сверки
    $peo = [67, 71, 72, 73, 89];
    $mtr = [90, 91, 92];
    $omszpz = [85, 84, 86];
    $leadership = [11, 88];
    $accountant = [134];
    $msgs = Message::withTrashed()->where('type_id',5)->get();
    
    foreach ($msgs as $msg) {
        $toUsers = $msg->to;
        $toUser = null;
        foreach ($toUsers as $u) {
            if ($u->hasPermissionTo('sign-mo-lider reconciliation-act')) {
                 $toUser = $u;
            }
        }

        $st = mb_substr($msg->subject,8);
        $toOrg  = Organization::where('short_name',$st)->first();
        if(isset($toOrg)){
            echo $toOrg->name;
            echo '<br>';
        } else {
            echo 'NO';
            echo '<br>';
        }
  
      
        $orgUsers = $toOrg->users()->with('permissions')->get();

        // Добавляем пользователей подписывающих акты 
        // со стороны мед.организации
        $attachUsersArr = [];
        foreach ($orgUsers as $u) {
            if (
                $u->hasPermissionTo('sign-mo-lider reconciliation-act')
                || $u->hasPermissionTo('sign-mo-accountant reconciliation-act')
            ) {
                $attachUsersArr[] = $u->id;
                echo $u->name;
                 echo '<br>';
            }
        }
        echo '<br>';
        // со стороны ТФОМС
        $attachUsersArr = array_merge(
            $attachUsersArr,
            $mtr,
            $leadership,
            $accountant
        );
        
        $attachUsersArr = array_unique($attachUsersArr, SORT_NUMERIC);
        $msg->to()->sync($attachUsersArr);//WithoutDetaching
    }
    return 'ok2';
    */
    
    
    
    //$permission = Permission::where('name', 'reject mek')->first();
    //$role = Role::where('name', 'leadership')->first();
    //$role->givePermissionTo($permission);
    
    // $permission = Permission::create(['name' => 'reject mek']);

    // $permission->delete();
    // $role = Role::create(['name' => 'tf-chief-accountant']);
    // 
    // 
    // $role->revokePermissionTo($permission);
    //
    // $user->removeRole('mo');
    // $user->assignRole('mo-chief-accountant');    
    
   
    return 'ok';
    
    // return User::doesntHave('roles')->get();


    $users = $role->users;
    
    foreach($users as $user) {
        echo "{$user->id} {$user->name} {$user->job_title} {$user->hasPermissionTo('sign-mo-lider mek')}";
        echo '<br>';
    }
    
    return 'ok';

    
    
    $permissions = $user->getAllPermissions()->pluck('name');
    //$b = array_map(function($p){return $p['name'];}, $permissions);
    return $permissions;
    /*
    $cryptoCert = CryptoCert::where('thumbprint',123)->first();
    if ($cryptoCert != null) {
        return 'not null';
    } else {
        return 'null';
    }
    return $cryptoCert;
    */    
    
    /*
    $files = FileSignStamp::where('stamped_file_path',null)->get();
    foreach($files as $file)
    {
        CreateSignStamp::dispatch(File::find($file->file_id));
    }
    */

   

    //return phpinfo();
});