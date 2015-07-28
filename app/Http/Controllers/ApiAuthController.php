<?php

namespace smarthome\Http\Controllers;
use Log;
use Auth;
use smarthome\User;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

class ApiAuthController extends Controller
{
    public function login(Request $request){
        $email = $request->input('email');
        $password = $request->input('password');
        Log::info('email: '.$email.'password: '.$password);
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Authentication passed...
            Log::info('login successful');
            $res = $request->user()->toArray();
            $res['result'] = 'success';
            return json_encode($res);
        }else{
            Log::info('login failed');
            return json_encode(array('result'=>'failed'));
        }
    }

    public function register(Request $request){
        $email = $request->input('email');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $password = $request->input('password');

        if(!$name){
             $name = "";
        }
        Log::info('register info, email: '.$email.'password: '.$password);

        $bExistEmail= User::where('email', $email)->count() > 0;
        if($bExistEmail){
             return json_encode(array('result'=>'failed', 'reason' => 'This email has already registed'));
        }

        $bExistPhone= User::where('phone', $phone)->count() > 0;
        if($bExistPhone){
             return json_encode(array('result'=>'failed', 'reason' => 'This phone number has already registed'));
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => bcrypt($password),
        ]);
        return $this->login($request);
    }
}
