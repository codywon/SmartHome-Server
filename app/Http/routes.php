<?php

use smarthome\User;
use smarthome\Device;
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

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

Route::get('/users', function () {
    return User::all()->toJson();
});

Route::get('/devices', function () {
    return Device::all()->toJson();
});

Route::get('/user/{id}', function ($id) {
    return User::find($id)->toJson();
});

Route::get('/user/{id}/devices', function ($id) {
    return User::find($id)->devices->toJson();
});

Route::get('/device/{id}', function ($id) {
    return Device::find($id)->toJson();
});

Route::get('/device/{id}/property', function ($id) {
    return Device::find($id)->properties->toJson();
});
