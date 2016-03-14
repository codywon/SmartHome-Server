<?php
use smarthome\User;
use smarthome\Device;
use smarthome\Scene;
use smarthome\Message;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('welcome');
});

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

Route::group(['prefix' => 'api'], function(){

    Route::post('/login', 'ApiAuthController@login');
    Route::post('/register', 'ApiAuthController@register');

    Route::post('/user/islogin', 'ApiUserController@isLogin');
    Route::post('/user/modifyname', 'ApiUserController@modifyNickname');
    Route::post('/user/modifysex', 'ApiUserController@modifySex');
    Route::post('/user/avatar/upload', 'ApiUserController@uploadAvatar');
    Route::get('/user/avatar/download', 'ApiUserController@downloadAvatar');

    Route::post('/password/set', 'ApiAuthController@setPassword');
    Route::post('/password/verify', 'ApiUserController@verifyPassword');
    Route::post('/password/modify', 'ApiUserController@modifyPassword');

    Route::post('/sms/apply', 'SMSController@apply');
    Route::post('/sms/verify', 'SMSController@verify');

    Route::resource('device', 'ApiDeviceController',
                    ['only' => ['index', 'show', 'store']]);
    Route::post('/device/update/{id}', 'ApiDeviceController@update');
    Route::post('/device/delete/{id}', 'ApiDeviceController@destroy');
    Route::post('/device/action', 'ApiDeviceController@action');
    Route::post('/device/search', 'ApiDeviceController@search');
    Route::post('/device/discover', 'ApiDeviceController@discover');
    Route::post('/device/status', 'ApiDeviceController@status');

    Route::resource('scene', 'ApiSceneController',
                    ['only' => ['index', 'show', 'store']]);
    Route::post('/scene/update/{id}', 'ApiSceneController@update');
    Route::post('/scene/delete/{id}', 'ApiSceneController@destroy');
    Route::post('/scene/open/{id}', 'ApiSceneController@open');
    Route::get('/scene/query/firstsix', 'ApiSceneController@firstsix');
    Route::post('/scene/query/firstsix', 'ApiSceneController@firstsix');

    Route::resource('room', 'ApiRoomController',
                    ['only' => ['index', 'show', 'store']]);
    Route::post('/room/update/{id}', 'ApiRoomController@update');
    Route::post('/room/delete/{id}', 'ApiRoomController@destroy');
    Route::get('/room/{id}/device', 'ApiRoomController@getDevice');
    Route::post('/room/{id}/device', 'ApiRoomController@addDevice');

    Route::get('/control', 'ApiUserController@control');

    Route::post('/group', 'ApiGroupController@store');
    Route::post('/group/join', 'ApiGroupController@join');
    Route::post('/group/quit', 'ApiGroupController@quit');
    Route::post('/group/query', 'ApiGroupController@show');
    Route::post('/group/resetpassword', 'ApiGroupController@resetpassword');
    Route::post('/group/destory', 'ApiGroupController@destroy');
    Route::post('/group/remove/{username}', 'ApiGroupController@remove');


    Route::get('/trigger', 'ApiTriggerController@index');
    Route::post('/trigger', 'ApiTriggerController@store');
    Route::get('/trigger/{id}', 'ApiTriggerController@show');
    Route::post('/trigger/delete/{id}', 'ApiTriggerController@destroy');
    Route::post('/trigger/update/{id}', 'ApiTriggerController@update');
});
