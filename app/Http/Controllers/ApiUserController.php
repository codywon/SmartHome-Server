<?php

namespace smarthome\Http\Controllers;

use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use Log;
use Auth;
use File;
use Storage;
use smarthome\User;
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
                $res['error'] = 0;
                return json_encode($res);
            }else{
                Log::info('phone '.$phone.' verify failed');
                return json_encode(array('error'=>101, 'reason'=>'用户名/密码错误'));
            }
        }
    }

    public function modifyPassword(Request $request){
        if(Auth::check()){
            $user = Auth::user();
            $password = $request->input('password');

            if(!$password){
                Log::info('set password failed, password is empty');
                return json_encode(array('error'=>109, 'reason'=>'参数错误'));
            }

            $user->password = bcrypt($password);
            $user->save();

            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[ModifyPassword] user is not login');
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

            $filePath = storage_path().'avatars/'.$user->id.'.jpg';
            $contentType = 'image/jpeg';
            if(File::exists($filePath)){
                $fileContents = File::get($filePath);
                return Response::make($fileContents, 200, array('Content-Type' => $contentType));
            }else{
                Log::error('download avatar failed, file does not exist!');
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
}
