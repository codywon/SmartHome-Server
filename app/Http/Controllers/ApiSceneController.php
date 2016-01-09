<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Scene;
use smarthome\SceneLRU;
use smarthome\DeviceCommand;

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
            Log::info('get all scenes, uid:'.$user->id);

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
            Log::error('get all scenes failed, user is not login');
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

            $name = $request->input('name');
            $devices = $request->input('devices');

            Log::info('add new scene, uid:'.$user->id.' name:'.$name.' devices:'.$devices);

            $scene = new Scene([
                'name' => $name,
                'devices' => $devices,
            ]);

            $user->scenes()->save($scene);

            $res = $scene->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('add new scene failed, user is not login');
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
            Log::info('query scene, uid:'.$user->id.' scene id:'.$id);

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('query scene, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Scene::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query scene failed, user is not login');
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
            Log::info('get first six scene, uid: '.$user->id);

            $res = array();
            $res['error'] = 0;

            $scenesArray = array();
            $sceneIDs = SceneLRU::getFirstSixScene($user->id);
            if(count($sceneIDs) == 0){
                $res['scenes'] = $user->scenes->toArray();
                return json_encode($res);
            }

            foreach ($sceneIDs as $id){
                $scene = $user->scenes()->find($id);
                if(!is_null($scene)){
                    array_push($scenesArray, $scene->toJson());
                }
            }
            $res['scenes'] = $scenesArray;

            return json_encode($res);
        }else{
            Log::error('get first six scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function open(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('open scene, uid: '.$user->id.' scene id:'.$id);

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('open scene failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $devices = $scene->devices;

            $params = array();
            $params['type'] = 100;
            $params['devices'] = $devices;

            DeviceCommand::sendMessage($user->id, $params, false, true);

            ob_end_clean();

            return json_encode(array('error'=>0));
        }else{
            Log::error('open scene failed, user is not login');
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
            Log::info('update scene, uid:'.$user->id.' scene id:'.$id);

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('update scene failed, uid:'.$user->id.' no such item:'.$id);
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
            Log::error('update scene failed, user is not login');
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
            Log::info('delete scene, uid: '.$user->id.' scene id:'.$id);

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                Log::error('delete scene failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Scene::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('delete scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
