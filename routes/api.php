<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\GroupController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([  'prefix' => 'auth' ] , function() {
    Route::post('/register' , [ LoginController::class , 'register']);
    Route::post('/login' , [ LoginController::class , 'login']);
    Route::post('/logout' , [ LoginController::class , 'logout']);
    Route::post('/refresh', [LoginController::class, 'refresh']);

});


Route::get('/files', [FileController::class, 'all_files']);
Route::post('/storeFiles', [FileController::class, 'store'])->middleware('CheckUser:user');
Route::post('/restoreFile', [FileController::class, 'restore_file']);
Route::get('/download/{id}',[FileController::class,'download'])->middleware('CheckUser:user');
Route::post('/update/{file_id}',[FileController::class,'update'])->middleware('CheckUser:user');
Route::get('/files/request', [FileController::class, 'requestFile']);
Route::put('/files/{id}/modify', [FileController::class, 'modifyFile']);
Route::get('/files/review/{status}', [FileController::class, 'reviewFilesByStatus']);
Route::delete('destroy/{file_id}',[FileController::class,'destroy']);
Route::get('get-reports',[FileController::class,'getreports']);
Route::get('get-reserved',[FileController::class,'getallfilereserved']);




 Route::group(['prefix' => 'group', 'middleware' => 'CheckUser'], function () {
    Route::post('store',[GroupController::class,'createGroup']);
    Route::get('add-file/{group_id}/{file_id}',[GroupController::class,'addfiletogroup']);
    Route::delete('add-file/{group_id}/{file_id}',[GroupController::class,'removefilefromgroup']);
    Route::get('join-group/{group_id}',[GroupController::class,'joingroup']);
    Route::delete('remove-member/{group_id}/{user_id}', [GroupController::class,'removeMembergroup']);
    Route::delete('Delete-Group/{group_id}', [GroupController::class,'destroy']);
    Route::get('get-all-groups', [GroupController::class,'getallgroups']);
    Route::get('get-all-groups-withuser', [GroupController::class,'getallgroupwithuser']);
    Route::get('group-withuser/{group_id}', [GroupController::class,'getgroupdetails']);
});


Route::post('book',[FileController::class,'bulk_check_in']);

Route::get('get_files_user/{user_id}',[FileController::class,'getallfileuser']);
