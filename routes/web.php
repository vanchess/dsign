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


Route::get('/', function () {

    //phpinfo();
    //CreateSignStamp::dispatch(File::find(36451));
    //$certs = File::find(19684)->signCerts()->distinct()->get();
    //return $certs;
  /*
    $permission = Permission::create(['name' => 'send agreement-fin-salaries']);
    $role = Role::where('name', 'fin')->first();
    $role->givePermissionTo($permission);
  */
  /*
    $permission = Permission::where('name', 'send reconciliation-act')->first();
    $role = Role::where('name', 'leadership')->first();
    //$role = Role::create(['name' => 'tfoms']);
    //$role->revokePermissionTo($permission);
    //$role->givePermissionTo($permission);
  */

    //$permission->delete();


/*
    // $signs = FileSign::All();
    $signs = FileSign::whereIn('id',[115908])->get();
    // $signs = FileSign::where('user_id',158)->get();
    // $signs = FileSign::whereNull('verified_on_server_at')->get();
    // $signs = FileSign::where('id','>',81899)->where('id','<',81914)->get();
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
    //$user = User::find(36);
    //$user->assignRole('dzo');
    //$user->assignRole('dzo-lider');
    //$user->removeRole('mo-lider');
    //$user->assignRole('smo-lider');
    //$user = User::find(55);
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

    $msgs = Message::whereIn('id',[35204, 35213])->get();
    //$msgs = Message::where('type_id',3)->where('created_at','>','2022-01-18')->where('created_at','<','2022-01-20')->get();
    //$msgs = Message::where('organization_id',38)->where('created_at','>','2022-07-28')->get();//
    foreach ($msgs as $msg) {
        // Пометить подписи пользователя как удаленные
        //foreach($msg->files as $f) {
        //    foreach($f->signs as $s) {
        //        if ($s->user_id === 11) {
        //            //echo $s->user_id . ' ';
        //           $s->delete();
        //        }
        //    }
        //}
        CheckMessageStatus::dispatch($msg);
    }
    return 'ok';
*/




//$users = PD::all();
//return $users;
/*
    $monthNum = '09';
    $year = '2022';
    //$tz = new DateTimeZone( '+0500' );
    $month = new DateTime("${year}-${monthNum}-01T00:00:00.000000+0500");
    $from = new DateTime("first day of {$month->format('F')} ${year}+0500");
    $to = new DateTime("last day of {$month->format('F')} ${year}T23:59:59.999999+0500");
    $from->setTimezone(new DateTimeZone('UTC'));
    $to->setTimezone(new DateTimeZone('UTC'));
    Period::firstOrCreate(['from' => $from, 'to' => $to]);

    return $monthNum;
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
*/

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
