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
    Route::post('/islogin', 'ApiAuthController@isLogin');
    Route::post('/modifyname', 'ApiAuthController@modifyNickname');

    Route::post('/password/set', 'ApiAuthController@setPassword');
    Route::post('/password/verify', 'ApiAuthController@verifyPassword');
    Route::post('/password/modify', 'ApiAuthController@modifyPassword');

    Route::post('/sms/apply', 'SMSController@apply');
    Route::post('/sms/verify', 'SMSController@verify');

    Route::resource('device', 'ApiDeviceController',
                    ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    Route::post('/device/action', 'ApiDeviceController@action');
    Route::post('/device/discover', 'ApiDeviceController@discover');
    Route::post('/device/status', 'ApiDeviceController@status');

    Route::post('/scene/{id}/open', 'ApiSceneController@open');
    Route::get('/scene/firstsix', 'ApiSceneController@firstsix');

    Route::resource('room', 'ApiRoomController',
                    ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
    Route::get('/room/{id}/device', 'ApiRoomController@getDevice');
    Route::post('/room/{id}/device', 'ApiRoomController@addDevice');

});
