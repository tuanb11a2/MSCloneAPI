<?php

use Illuminate\Support\Facades\Route;
use Modules\ManagerApi\Http\Controllers\AuthController;
use Modules\ManagerApi\Http\Controllers\GroupController;
use Modules\ManagerApi\Http\Controllers\UserController;

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
Route::middleware('lang')->prefix('manager')->group(function () {
    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth.jwt');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth.jwt');
        Route::get('user', [AuthController::class, 'me'])->middleware('auth.jwt');;
    });

    Route::middleware('auth.jwt')->group(function () {
        Route::get('groups/others', [GroupController::class, 'getOtherGroups']);
        Route::resource('groups', GroupController::class)->except(['create', 'edit']);
        Route::get('groups/{group}/users', [GroupController::class, 'getUsers']);
        Route::get('/groups/{groupId}/leave', [GroupController::class, 'leaveGroup']);
        Route::get('/groups/{groupId}/join/{userId}', [GroupController::class, 'joinGroup']);

        Route::delete('/groups/{groupId}/remove-member/{memberId}', [GroupController::class, 'removeMember']);
        Route::post('/groups/{groupId}/add-members', [GroupController::class, 'addMembers']);

        Route::get('/groups/{groupSlug}/channels/{channelSlug}', [GroupController::class, 'getChannel']);
        Route::post('/groups/{groupId}/channels', [GroupController::class, 'addChannel']);
        Route::put('/groups/{groupId}/channels/{channelId}', [GroupController::class, 'updateChannel']);
        Route::delete('/groups/{groupId}/channels/{channelId}', [GroupController::class, 'deleteChannel']);

        Route::resource('users', UserController::class)->except(['create', 'edit']);
    });
});
