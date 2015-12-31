<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Scene;

class ApiSceneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [INDEX] user info: '.$user->toJson());

            $scenes = $user->scenes->toArray();
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $user->scenes->count();
            $res['error'] = 0;
            $res['scenes'] = $scenes;
            return json_encode($res);

        }else{
            Log::error('[SCENE] [INDEX] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [ADD] user info: '.$user->toJson());

            $name = $request->input('name');
            $devices = $request->input('devices');

            Log::info('[SCENE] [ADD] devices: '.$devices.'name: '.$name);

            $scene = new Scene([
                'name' => $name,
                'devices' => $devices,
            ]);

            $user->scenes()->save($scene);

            $res = $scene->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[SCENE] [ADD] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [QUERY] user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('[SCENE] [QUERY] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Scene::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[SCENE] [QUERY] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }
    /**
    * Get the first six scene
    */
    public function firstsix()
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [FirstSix] user info: '.$user->toJson());

            $res = array();
            $res['error'] = 0;
            $sceneIDs = SceneLRU::getFisrtSixScene($user->id);
            foreach ($sceneIDs as $id){
                $scene = $user->scenes()->find($id);
                if(!is_null($scene)){
                    $res[$id] = $scene->toArray();
                }
            }

            return json_encode($res);
        }else{
            Log::error('[SCENE] [FirstSix] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function open(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [UPDATE] user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('[SCENE] [UPDATE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $devices = $scene->devices();

            $params = array();
            $params['type'] = 100;
            $params['devices'] = $devices;

            DeviceCommand::sendMessage($user->id, $params, false, true);

            return json_encode(array('error'=>0));
        }else{
            Log::error('[SCENE] [UPDATE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [UPDATE] user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('[SCENE] [UPDATE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $name = $request->input("name");
            if(!empty($name)){
                 $room->name = $name;
            }

            $devices = $request->input("devices");
            if(!empty($devices)){
                 $room->devices = $devices;
            }

            $scene->save();

            $res = $scene->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[SCENE] [UPDATE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[SCENE] [DELETE] user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('[SCENE] [DELETE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Scene::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('[SCENE] [DELETE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
