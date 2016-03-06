<?php

namespace smarthome\Http\Controllers;

use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use Log;
use Auth;

use smarthome\Device;
use smarthome\User;

class ApiGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Create a new family group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('create family group, uid: '.$user->id);

            if(!empty($user->group)){
                Log::error('create family group failed, user:'.$user->id.' has already joined another family group');
                return json_encode(array('error'=>501, 'reason'=>'该用户已加入家庭组'));
            }

            $group_name = $request->input('groupname');
            if(empty($group_name)){
                Log::error('create family group failed, user:'.$user->id.' miss parameter [groupname]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[groupname]'));
            }

            $bExistGroup = User::where('group', $group_name)->count() > 0;
            if($bExistGroup){
                Log::error('create family group failed, user:'.$user->id.' miss parameter [groupname]');
                return json_encode(array('error'=>503, 'reason'=>'该家庭组名已被注册'));
            }

            $password  = $request->input('password');
            if(empty($password)){
                Log::error('create family group failed, user:'.$user->id.' miss parameter [password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[password]'));
            }

            if(strlen($password) < 8){
                Log::error('create family group failed, user:'.$user->id.' wrong format of password');
                return json_encode(array('error'=>108, 'reason'=>'密码长度不符合要求, 最少8位'));
            }

            $user->group = $group_name;
            $user->role = 1; // 1 means administrator, 2 means commer user
            $user->group_password = md5($password);
            $user->save();

            // modify all user's devices, fill group filed
            $devices = $user->devices;
            foreach ($devices as $device) {
                $device->group = $group_name;
                $device->save();
            }

            // modify all user's rooms, fill group filed
            $rooms = $user->rooms;
            foreach ($rooms as $room) {
                $room->group = $group_name;
                $room->save();
            }

            // modify all user's scenes, fill group filed
            $scenes = $user->scenes;
            foreach ($scenes as $scene) {
                $scene->group = $group_name;
                $scene->save();
            }

            // modify all user's messages, fill group filed
            $messages = $user->messages;
            foreach ($messages as $message) {
                $message->group = $group_name;
                $message->save();
            }

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function join(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('join family group, uid: '.$user->id);

            if(!empty($user->group)){
                Log::error('join family group failed, user:'.$user->id.' has already joined another family group');
                return json_encode(array('error'=>501, 'reason'=>'该用户已加入家庭组'));
            }

            $group_name = $request->input('groupname');
            if(empty($group_name)){
                Log::error('join family group failed, user:'.$user->id.' miss parameter [groupname]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[groupname]'));
            }

            $bExistGroup = User::where('group', $group_name)->count() > 0;
            Log::info("exist:".$bExistGroup);
            if(!$bExistGroup){
                Log::error('join family group failed, user:'.$user->id.', specify group['.$group_name.'] was inexistence');
                return json_encode(array('error'=>507, 'reason'=>'指定家庭组不存在'));
            }

            $password  = $request->input('password');
            if(empty($password)){
                Log::error('join family group failed, user:'.$user->id.' miss parameter [password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[password]'));
            }

//            $groupAdmin = User::whereRaw('group = ? and role = 1', [$group_name])->get()->first();
            $groupAdmin = User::where('group', '=', $group_name)->where('role', '=', 1)->first();
            if(is_null($groupAdmin)){
                Log::error('join family group failed, user:'.$user->id.', specify group['.$group_name.'] was inexistence');
                return json_encode(array('error'=>507, 'reason'=>'指定家庭组不存在'));
            }

            if(strcmp(md5($password), $groupAdmin->group_password) != 0){
                Log::error('join family group failed, user:'.$user->id.', wrong group password');
                return json_encode(array('error'=>508, 'reason'=>'家庭组密码错误'));
            }

            $user->group = $group_name;
            $user->role = 2; // 1 means administrator, 2 means commer user
            $user->group_password = md5($password);
            $user->save();

            // modify all user's devices, fill group filed
            $devices = $user->devices;
            foreach ($devices as $device) {
                $device->group = $group_name;
                $device->save();
            }

            // modify all user's rooms, fill group filed
            $rooms = $user->rooms;
            foreach ($rooms as $room) {
                $room->group = $group_name;
                $room->save();
            }

            // modify all user's scenes, fill group filed
            $scenes = $user->scenes;
            foreach ($scenes as $scene) {
                if(!$scene->is_default){    // skip default scenes
                    $scene->group = $group_name;
                    $scene->save();
                }
            }

            // modify all user's messages, fill group filed
            $messages = $user->messages;
            foreach ($messages as $message) {
                $message->group = $group_name;
                $message->save();
            }

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }


    public function quit(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('quit family group, uid: '.$user->id);

            if(empty($user->group)){
                Log::error('quit family group failed, user:'.$user->id.' was not joined any family group');
                return json_encode(array('error'=>504, 'reason'=>'未加入任何家庭组'));
            }

            if($user->role == 1){   // 1 means administrator of family group
                Log::error('quit family group failed, user:'.$user->id.', you are creater, please destory directly');
                return json_encode(array('error'=>509, 'reason'=>'家庭组创建者，请直接删除家庭组'));
            }

            $login_password  = $request->input('login_password');
            if(empty($login_password)){
                Log::error('reset family group password failed, user:'.$user->id.' miss parameter [login_password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[login_password]'));
            }
            if (!Auth::attempt(['phone' => $user->phone, 'password' => $login_password])) {
                Log::error('reset family group password failed, user:'.$user->id.' verify password failed');
                return json_encode(array('error'=>125, 'reason'=>'密码验证失败'));
            }

            $user->group = '';
            $user->role = 0;
            $user->group_password = '';
            $user->save();

            // reset all group field of specified user's devices
            $devices = $user->devices;
            foreach ($devices as $device) {
                $device->group = '';
                $device->save();
            }

            // reset all group field of specified user's rooms
            $rooms = $user->rooms;
            foreach ($rooms as $room) {
                $room->group = '';
                $room->save();
            }

            // reset all group field of specified user's scenes
            $scenes = $user->scenes;
            foreach ($scenes as $scene) {
                $scene->group = '';
                $scene->save();
            }

            // reset all group field of specified user's messages
            $messages = $user->messages;
            foreach ($messages as $message) {
                $message->group = '';
                $message->save();
            }

            Log::info($user->name.' quit from family group'.$user->group.' success, uid: '.$user->id);

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
    /**
     * Display all users in specified group
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('query family group, uid: '.$user->id);

            if(empty($user->group)){
                Log::error('query family group failed, user:'.$user->id.' was not joined any family group');
                return json_encode(array('error'=>504, 'reason'=>'未加入任何家庭组'));
            }

            $userArray = array();
            $users = User::where('group', $user->group)->get();
            foreach ($users as $user) {
                $userinfo = array();
                $userinfo['name'] = $user->name;
                $userinfo['phone'] = $user->phone;
                array_push($userArray, $userinfo);
            }

            $res['error'] = 0;
            $res['users'] = $userArray;
            return json_encode($res);
        }else{
            Log::error('query device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified family group.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('destroy family group, uid: '.$user->id);

            if(empty($user->group)){
                Log::error('destroy family group failed, user:'.$user->id.' was not joined any family group');
                return json_encode(array('error'=>504, 'reason'=>'未加入任何家庭组'));
            }

            if($user->role != 1){   // 1 means administrator of family group
                Log::error('destroy family group failed, user:'.$user->id.', permission denied');
                return json_encode(array('error'=>505, 'reason'=>'无权限'));
            }

            $login_password  = $request->input('login_password');
            if(empty($login_password)){
                Log::error('reset family group password failed, user:'.$user->id.' miss parameter [login_password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[login_password]'));
            }
            if (!Auth::attempt(['phone' => $user->phone, 'password' => $login_password])) {
                Log::error('reset family group password failed, user:'.$user->id.' verify password failed');
                return json_encode(array('error'=>125, 'reason'=>'密码验证失败'));
            }

            $group = $user->group;

            // reset all group field of all related users
            $users = User::where('group', $group)->get();
            foreach ($users as $user) {
                $user->group = '';
                $user->role = 0;
                $user->group_password = '';
                $user->save();

                // reset all group field of specified user's devices
                $devices = $user->devices;
                foreach ($devices as $device) {
                    $device->group = '';
                    $device->save();
                }

                // reset all group field of specified user's rooms
                $rooms = $user->rooms;
                foreach ($rooms as $room) {
                    $room->group = '';
                    $room->save();
                }

                // reset all group field of specified user's scenes
                $scenes = $user->scenes;
                foreach ($scenes as $scene) {
                    $scene->group = '';
                    $scene->save();
                }

                // reset all group field of specified user's messages
                $messages = $user->messages;
                foreach ($messages as $message) {
                    $message->group = '';
                    $message->save();
                }
            }

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function remove(Request $request, $username){
        if(Auth::check()){
            $user = Auth::user();

            if(empty($user->group)){
                Log::error('remove specify user from family group failed, user:'.$user->id.' was not joined any family group');
                return json_encode(array('error'=>504, 'reason'=>'未加入任何家庭组'));
            }

            if($user->role != 1){   // 1 means administrator of family group
                Log::error('destroy family group failed, user:'.$user->id.', permission denied');
                return json_encode(array('error'=>505, 'reason'=>'无权限'));
            }

            $login_password  = $request->input('login_password');
            if(empty($login_password)){
                Log::error('reset family group password failed, user:'.$user->id.' miss parameter [login_password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[login_password]'));
            }
            if (!Auth::attempt(['phone' => $user->phone, 'password' => $login_password])) {
                Log::error('reset family group password failed, user:'.$user->id.' verify password failed');
                return json_encode(array('error'=>125, 'reason'=>'密码验证失败'));
            }

            $bExistSpecifyUser = User::where('name', $username)->count() > 0;
            if($bExistSpecifyUser){
                $specifyUser = User::where('name', $username)->first();
                if(strcmp($specifyUser->group, $user->group) == 0){
                    $specifyUser->group = '';
                    $specifyUser->role = 0;
                    $specifyUser->group_password = '';
                    $specifyUser->save();

                    // reset all group field of specified user's devices
                    $devices = $specifyUser->devices;
                    foreach ($devices as $device) {
                        $device->group = '';
                        $device->save();
                    }

                    // reset all group field of specified user's rooms
                    $rooms = $specifyUser->rooms;
                    foreach ($rooms as $room) {
                        $room->group = '';
                        $room->save();
                    }

                    // reset all group field of specified user's scenes
                    $scenes = $specifyUser->scenes;
                    foreach ($scenes as $scene) {
                        $scene->group = '';
                        $scene->save();
                    }

                    // reset all group field of specified user's messages
                    $messages = $specifyUser->messages;
                    foreach ($messages as $message) {
                        $message->group = '';
                        $message->save();
                    }

                    Log::info('remove '.$username.' from family group'.$user->group.' success, uid: '.$user->id);

                    $res['error'] = 0;
                    return json_encode($res);
                }
            }

            Log::error('remove specify user from family group failed, wrong parameters');
            return json_encode(array('error'=>506, 'reason'=>'参数错误'));

        }else{
            Log::error('remove device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function resetpassword(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('reset family group password, uid: '.$user->id);

            if(empty($user->group)){
                Log::error('reset family group password failed, user:'.$user->id.' was not joined any family group');
                return json_encode(array('error'=>504, 'reason'=>'未加入任何家庭组'));
            }

            if($user->role != 1){   // 1 means administrator of family group
                Log::error('reset family group password failed, user:'.$user->id.', permission denied');
                return json_encode(array('error'=>505, 'reason'=>'无权限'));
            }

            $login_password  = $request->input('login_password');
            if(empty($login_password)){
                Log::error('reset family group password failed, user:'.$user->id.' miss parameter [login_password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[login_password]'));
            }

            if (!Auth::attempt(['phone' => $user->phone, 'password' => $login_password])) {
                Log::error('reset family group password failed, user:'.$user->id.' verify password failed');
                return json_encode(array('error'=>125, 'reason'=>'密码验证失败'));
            }

            $group_password  = $request->input('group_password');
            if(empty($group_password)){
                Log::error('reset family group password failed, user:'.$user->id.' miss parameter [group_password]');
                return json_encode(array('error'=>502, 'reason'=>'缺少参数[group_password]'));
            }

            if(strlen($group_password) < 8){
                Log::error('create family group failed, user:'.$user->id.' wrong format of password');
                return json_encode(array('error'=>108, 'reason'=>'密码长度不符合要求, 最少8位'));
            }

            $user->group_password = md5($group_password);
            $user->save();

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
