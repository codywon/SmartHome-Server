<?php

namespace smarthome\Http\Controllers;

use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use Log;
use Auth;
use File;
use smarthome\Security;
use Storage;
use Illuminate\Http\Response;
use smarthome\User;
use smarthome\Device;
use smarthome\Room;
use smarthome\SMS;
use GuzzleHttp\Client;

class ApiUserController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function modifyNickname(Request $request){
        if(Auth::check()){
            $user = Auth::user();

            $nickname = $request->input('nickname');
            Log::info('modify nickname, uid:'.$user->id);

            $user->name = $nickname;
            $user->save();

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('modify nickname failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function isLogin()
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('check login status, uid: '.$user->id.' is logined');

            $res['login'] = true;
            return json_encode($res);

        }else{
            Log::info('check login status, user is not login');
            $res['login'] = false;
            return json_encode($res);
        }
    }

    public function verifyPassword(Request $request){
        $phone = $request->input('phone');
        $password = $request->input('password');

        if(!$phone){
            Log::info('wrong request parameters, phone is empty');
            return json_encode(array('error'=>105, 'reason'=>'用户名不能为空'));
        }

        if(!$password){
            Log::info('wrong request parameters, password is empty');
            return json_encode(array('error'=>106, 'reason'=>'密码不能为空'));
        }

        if(!empty($phone)){
            if (Auth::attempt(['phone' => $phone, 'password' => $password])) {
                // Authentication passed...
                Log::info('verify password successful for phone:'.$phone);

                // write result to redis
                Security::writeVerifyResultToRedis($phone, true);

                $res['error'] = 0;
                return json_encode($res);
            }else{
                Security::writeVerifyResultToRedis($phone, false);

                Log::info('phone '.$phone.' verify failed');
                return json_encode(array('error'=>101, 'reason'=>'用户名/密码错误'));
            }
        }
    }

    public function modifySex(Request $request){
        if(Auth::check()){
            $user = Auth::user();
            $sex = $request->input('sex');

            if(!$sex){
                Log::info('modify sex failed, parameter sex is empty');
                return json_encode(array('error'=>109, 'reason'=>'参数错误'));
            }

            Log::info($sex);
            if($sex == 'true'){
                Log::info('modify sex as male, uid:'.$user->id);
            }else{
                Log::info('modify sex as female , uid:'.$user->id);
            }

            $user->sex = $sex == 'true' ? true : false;
            $user->save();

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('modify sex failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function modifyPassword(Request $request){
        if(Auth::check()){
            $user = Auth::user();
            $password = $request->input('password');

            if(!$password){
                Log::info('modify password failed, password is empty');
                return json_encode(array('error'=>109, 'reason'=>'参数错误'));
            }

            $user->password = bcrypt($password);
            $user->save();

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('modify password failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function uploadAvatar(Request $request){
         if(Auth::check()){
            $user = Auth::user();

            // store avatar to local disk
            if($request->hasFile('avatar')){
                Storage::put('avatars/'.$user->id.'.jpg', file_get_contents($request->file('avatar')->getRealPath()));
                Log::info('upload avatar successful, uid:'.$user->id);
            }else{
                Log::error('upload avatar failed, missing parameter: avatar');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [avatar]'));
            }

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('upload avatar failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function downloadAvatar(){
         if(Auth::check()){
            $user = Auth::user();

            $filePath = storage_path().'/app/avatars/'.$user->id.'.jpg';
            $contentType = 'image/jpeg';
            if(File::exists($filePath)){
                //$fileContents = File::get($filePath);
                return response()->download($filePath, $user->id.'.jpg', array('Content-Type' => $contentType));
            }else{
                Log::error('download avatar failed, file['.$filePath.'] does not exist!');
                return json_encode(array('error'=>131, 'reason'=>'用户未上传头像'));
            }
        }else{
            Log::error('download avatar failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function getImageContentType($file)
    {
        $mime = exif_imagetype($file);

        if ($mime === IMAGETYPE_JPEG)
            $contentType = 'image/jpeg';
        elseif ($mime === IMAGETYPE_GIF)
            $contentType = 'image/gif';
        else if ($mime === IMAGETYPE_PNG)
            $contentType = 'image/png';
        else
            $contentType = false;

         return $contentType;
    }

    public function control(){
        if(Auth::check()){
            $user = Auth::user();

            $controlInfos = array();
            $controlInfos['user_id'] = $user->id;

            $floorArrays = array();

            $rooms = $user->rooms;
            foreach ($rooms as $room) {
                $isExist = false;
                foreach ($floorArrays as $floorArray) {
                    if($floorArray["floor"] == $room->floor){
                        $isExist = true;
                        break;
                    }
                }

                if(!$isExist){
                    $floor = array();
                    $floor["floor"] = $room->floor;
                    $floor["rooms"] = array();
                    array_push($floorArrays, $floor);
                }
            }

            foreach ($rooms as $room) {
                for($i=0; $i<count($floorArrays); $i++) {
                    Log::info($room->name.' '.$room->floor.' '.$floorArrays[$i]["floor"]);
                    if($floorArrays[$i]["floor"] == $room->floor){
                        Log::info("equal");

                        $currRoom = array();
                        $currRoom["name"] = $room->name;
                        $currRoom["room_id"] = $room->id;
                        $currRoom["type"] = $room->type;
                        $deviceArray = $room->devices->toArray();
                        $currRoom["devices"] = $deviceArray;

                        Log::info('before:'.json_encode($floorArrays[$i]));
                        array_push($floorArrays[$i]["rooms"], $currRoom);
                        Log::info('after:'.json_encode($floorArrays[$i]));

                        break;
                    }
                }
            }

            $res = array();
            $res['error'] = 0;
            $res["floors"] = $floorArrays;
            return json_encode($res);

        }else{
            Log::error('download avatar failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
