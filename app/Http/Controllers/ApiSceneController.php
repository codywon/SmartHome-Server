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
use smarthome\SceneLRUByGroup;

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

            $scenes = null;
            $total = 0;
            if(empty($user->group)){
                $scenes = $user->scenes->toArray();
                $total = $user->scenes->count();
                Log::info('total:'.$total.' scenes:'.json_encode($scenes));
            }else{
                $scenes = Scene::where('group', $user->group)->get()->toArray();
                $total = Scene::where('group', $user->group)->count();
                Log::info('group mode, total:'.$total.' scenes:'.json_encode($scenes));
            }

            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $total;
            $res['error'] = 0;
            $res['scenes'] = $scenes;
            return json_encode($res);

        }else{
            Log::error('get all scenes failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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

            $count= Scene::where('user_id','=',$user->id)->where('name','=',$name)->count();
            if($count != 0){
                Log::error('create scene failed, user:'.$user->id.' already had scene with same name');
                return json_encode(array('error'=>401, 'reason'=>'该用户已经有相同名称的情景模式'));
            }
            $scene = new Scene([
                'name' => $name,
                'devices' => $devices,
                'group' => $user->group,
            ]);

            $user->scenes()->save($scene);

            $res = $scene->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('add new scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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

            $scene = null;
            if(empty($user->group)){
                $scene = $user->scenes()->find($id);
            }else{
                $scene = Scene::where('group', $user->group)->get()->find($id);
            }

            if(is_null($scene)){
                Log::error('query scene, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应情景模式'));
            }

            $res = Scene::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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
            $sceneIDs = null;
            if(empty($user->group)){
                $sceneIDs = SceneLRU::getFirstSixScene($user->id);
            }else{
                $sceneIDs = SceneLRUByGroup::getFirstSixScene($user->group);
            }

            if(count($sceneIDs) < 6){
                $res['scenes'] = $user->scenes->toArray();
                return json_encode($res);
            }

            foreach ($sceneIDs as $id){
                $scene = $user->scenes()->find($id);
                if(!is_null($scene)){
                    array_push($scenesArray, $scene->toArray());
                }
            }
            $res['scenes'] = $scenesArray;

            return json_encode($res);
        }else{
            Log::error('get first six scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function open(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('open scene, uid: '.$user->id.' scene id:'.$id);

            $scene = null;
            if(empty($user->group)){
                $scene = $user->scenes()->find($id);
            }else{
                $scene = Scene::where('group', $user->group)->get()->find($id);
            }

            if(is_null($scene)){
                Log::error('open scene failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应情景模式'));
            }

            $devices = $scene->devices;

            foreach(explode(';', $devices) as $id_action){
                $arr = array();
                parse_str($id_action, $arr);
                foreach($arr as $deviceID=>$action){
                    // TODO perform operations on a certain device
                    $device = Device::find($deviceID);
                    if(!is_null($device)){
                        $device->status = $action;
                        $device->save();
                    }
                }
            }

            $params = array();
            $params['type'] = 104;
            $params['devices'] = $devices;

            DeviceCommand::sendMessage($user->id, $params, false, true);

            // increase clicked times
            if(empty($user->group)){
                SceneLRU::incr($user->id, $scene->id);
            }else{
                SceneLRUByGroup::incr($user->group, $scene->id);
            }

            ob_end_clean();

            return json_encode(array('error'=>0));
        }else{
            Log::error('open scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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

            $scene = null;
            if(empty($user->group)){
                $scene = $user->scenes()->find($id);
            }else{
                $scene = Scene::where('group', $user->group)->get()->find($id);
            }

            if(is_null($scene)){
                Log::error('update scene failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应情景模式'));
            }

            $name = $request->input("name");
            if(!empty($name)){
                 $scene->name = $name;
            }

            $devices = $request->input("devices");
            if(!empty($devices)){
                 $scene->devices = $devices;
            }

            $scene->save();

            $res = $scene->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('update scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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

            $scene = null;
            if(empty($user->group)){
                $scene = $user->scenes()->find($id);
            }else{
                $scene = Scene::where('group', $user->group)->get()->find($id);
            }
            if(is_null($scene)){
                Log::error('delete scene failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应情景模式'));
            }

            Scene::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('delete scene failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
