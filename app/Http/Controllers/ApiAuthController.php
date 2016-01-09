<?php

namespace smarthome\Http\Controllers;
use Log;
use Auth;
use smarthome\SMS;
use smarthome\User;
use smarthome\Scene;
use smarthome\SceneLRU;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

class ApiAuthController extends Controller
{
    public function login(Request $request){
        $email = $request->input('email');
        $phone = $request->input('phone');
        $password = $request->input('password');
        Log::info('login info: phone:'.$phone);

        if(!$email && !$phone){
            Log::info('login failed, email/phone is empty');
            return json_encode(array('error'=>105));
        }

        if(!$password){
            Log::info('login failed, password is empty');
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
                Log::info('login successful by phone:'.$phone);
                $res = $request->user()->toArray();
                $res['error'] = 0;
                return json_encode($res);
            }else{
                Log::info('phone '.$phone.' login failed');
                return json_encode(array('error'=>101));
            }
        }
    }

    public function register(Request $request){
        $name = $request->input('nickname');
        $phone = $request->input('phone');
        $password = $request->input('password');

        if(!$name || !$phone ){
            Log::info('register failed, nickname/phone is empty');
            return json_encode(array('error'=>105));
        }

        if(!$password){
            Log::info('register failed, password is empty');
            return json_encode(array('error'=>106));
        }

        Log::info('register info, nickname:'.$name.' phone:'.$phone);

        $bExistPhone= User::where('phone', $phone)->count() > 0;
        if($bExistPhone){
            Log::error('phone['.$phone.'] has alread registed');
            return json_encode(array('error'=>103, 'reason' => 'This phone number has already registed'));
        }

        if(!SMS::isChecked($phone)){
            Log::error('check verify code failed');
            return json_encode(array('error'=>124));
        }

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => bcrypt($password),
        ]);

        $this->createDefaultScene($user);

        return $this->login($request);
    }

    private function createDefaultScene($user){

        $names = array("回家模式", "离家模式", "全开模式", "全关模式", "就餐模式", "安全模式");
        $icons = array("回家模式" => "BeHome",
                       "离家模式" => "LeaveHome",
                       "全开模式" => "OpenAll",
                       "全关模式" => "CloseAll",
                       "就餐模式" => "Eat",
                       "安全模式" => "Security");
        foreach($names as $name){
            $scene = new Scene([
                'name' => $name,
                'is_default' => true,
                'default_icon' => $icons[$name],
            ]);
            $user->scenes()->save($scene);
            SceneLRU::incr($user->id, $scene->id);
        }
        Log::debug('create default scene for user['.$user->name.']');
    }
}

