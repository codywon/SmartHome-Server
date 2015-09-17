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
        $phone = $request->input('phone');
        $password = $request->input('password');
        Log::info('email:'.$email.' phone:'.$phone.' password:'.$password);

        if(!$email && !$phone){
            Log::info('register failed, email/phone is empty');
             return json_encode(array('error'=>105));
        }

        if(!$password){
            Log::info('register failed, password is empty');
             return json_encode(array('error'=>106));
        }

        if(!empty($email)){
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                // Authentication passed...
                Log::info('login successful');
                $res = $request->user()->toArray();
                $res['error'] = 0;
                return json_encode($res);
            }else{
                Log::info('login failed');
                return json_encode(array('error'=>101));
            }
        }

        if(!empty($phone)){
            if (Auth::attempt(['phone' => $phone, 'password' => $password])) {
                // Authentication passed...
                Log::info('login successful');
                $res = $request->user()->toArray();
                $res['error'] = 0;
                return json_encode($res);
            }else{
                Log::info('login failed');
                return json_encode(array('error'=>101));
            }
        }
    }

    public function register(Request $request){
        $email = $request->input('email');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $password = $request->input('password');

        $bUserNameEmpty = false;
        if(!$name){
             $name = "";
        }
        if(!$email && !$phone){
            Log::info('register failed, email/phone is empty');
             return json_encode(array('error'=>105));
        }

        if(!$password){
            Log::info('register failed, password is empty');
             return json_encode(array('error'=>106));
        }

        if(!$email){
            $email = "";
        }

        if(!$phone){
             $phone = "";
        }

        Log::info('register info, name:'.$name.' email:'.$email.' phone:'.$phone.' password: '.$password);

        $bExistEmail= User::where('email', $email)->count() > 0;
        if($bExistEmail){
             return json_encode(array('error'=>102, 'reason' => 'This email has already registed'));
        }

        $bExistPhone= User::where('phone', $phone)->count() > 0;
        if($bExistPhone){
             return json_encode(array('error'=>103, 'reason' => 'This phone number has already registed'));
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
