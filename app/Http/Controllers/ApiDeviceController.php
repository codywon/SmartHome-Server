<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Device;

class ApiDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
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
            $type = $request->input('type');
            $room_id = $request->input('room_id');
            $brand = $request->input('brand');
            $model = $request->input('model');
            $bInfrared = $request->input('infrared') == 'true';

            Log::info('infrared value: '.$request->input('infrared').'type: '.$type.'name: '.$name);

            $device = new Device([
                'name' => $name,
                'type' => $type,
                'room_id' => $room_id,
                'brand' => $brand,
                'model' => $model,
                'infrared' => $bInfrared,
                'status' => 0,
            ]);

            $user->devices()->save($device);

            $res = $device->toArray();
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

            $device = $user->devices()->find($id);
            if(is_null($device)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Device::find($id)->toArray();
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

            $device = $user->devices()->find($id);
            if(is_null($device)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $name = $request->input("name");
            if(!empty($name)){
                 $device->name = $name;
            }

            $room_id = $request->input("room_id");
            if(!empty($room_id)){
                 $device->room_id = $room_id;
            }

            $brand = $request->input("brand");
            if(!empty($brand)){
                 $device->brand = $brand;
            }

            $model = $request->input("model");
            if(!empty($model)){
                 $device->model = $model;
            }

            $infrared = $request->input("infrared");
            if(!empty($infrared)){
                 $device->infrared = $infrared == "true";
            }
            $device->save();

            $res = $device->toArray();
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

            $device = $user->devices()->find($id);
            if(is_null($device)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Device::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
