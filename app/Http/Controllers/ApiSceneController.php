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
            Log::info('user info: '.$user->toJson());

            $res = $user->scenes->toArray();
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res['total'] = $user->scenes->count();
            $res['error'] = 0;
            return json_encode($res);

        }else{
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
            Log::info('user info: '.$user->toJson());

            $name = $request->input('name');
            $devices = $request->input('devices');

            Log::info('devices: '.$devices.'name: '.$name);

            $scene = new Scene([
                'name' => $name,
                'devices' => $devices,
            ]);

            $user->scenes()->save($scene);

            $res = $scene->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
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
            Log::info('user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Scene::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
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
            Log::info('user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
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
            Log::info('user info: '.$user->toJson());

            $scene = $user->scenes()->find($id);
            if(is_null($scene)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Scene::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
