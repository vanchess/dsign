<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispListEntriesController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileUpload;
use App\Http\Controllers\MessageStatusController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageToUsersController;
use App\Http\Controllers\MessageFilesController;
use App\Http\Controllers\MessageHasStatusController;
use App\Http\Controllers\FileFileSignController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MessageDispListsController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\PDController;
use App\Http\Controllers\PreventiveMedicalMeasureTypeController;
use App\Http\Controllers\UserRoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(array('prefix' => 'v1'), function()
{

    Route::middleware('auth:api')->group(function()
    {
        Route::post('/upload-file',  [FileUpload::class, 'fileUpload'])->name('fileUpload');
        Route::post('/upload-file-multiple',  [FileUpload::class, 'fileUploadMultiple'])->name('fileUploadMultiple');
        Route::get('/download-file/{id}', [FileUpload::class, 'fileDownload'])->where('id', '[0-9]+')->name('fileDownload');
        Route::get('/download-file-stamped/{id}', [FileUpload::class, 'fileStampedDownload'])->where('id', '[0-9]+')->name('fileStampedDownload');
        Route::get('/download-file-pdf/{id}', [FileUpload::class, 'filePdfDownload'])->where('id', '[0-9]+')->name('filePdfDownload');
        /*
        Route::get('user', function (Request $request) {
            return $request->user();
        });
        */

        Route::apiResource('my-files', FileController::class);
        Route::post('users/{userId}/assign-role/{roleName}', [UserRoleController::class, 'assignRole'])->where('userId', '[0-9]+');
        Route::post('users/{userId}/remove-role/{roleName}', [UserRoleController::class, 'removeRole'])->where('userId', '[0-9]+');
        Route::apiResources([
            'msg-status' => MessageStatusController::class,
            'msg' => MessageController::class,
            'users' => UserController::class,
            'period' => PeriodController::class,
            //'file-signs' => FileSignController::class,
            'preventive-medical-measure' => PreventiveMedicalMeasureTypeController::class
        ]);
        Route::apiResource('msg.to-users', MessageToUsersController::class);
        Route::apiResource('msg.files', MessageFilesController::class);
        Route::apiResource('msg.status', MessageHasStatusController::class)->only(['index', 'store']);
        Route::apiResource('msg.displists', MessageDispListsController::class)->only(['index']);
        Route::apiResource('file.sign', FileFileSignController::class);
        Route::apiResource('displist.entries', DispListEntriesController::class)->only(['index', 'store', 'update', 'destroy']);

    });

    /*
    Route::get('/users', function (Request $request) {
        return $request->user();
    });
    */

    Route::apiResource('organization', OrganizationController::class);
    Route::apiResource('invite', InviteController::class);
    Route::apiResource('pd', PDController::class);

    Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });
});
