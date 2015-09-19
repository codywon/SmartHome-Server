<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Device;
use smarthome\DeviceCommand;

class ApiDeviceController extends Controller
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
            Log::info('[DEVICE] [INDEX] user info: '.$user->toJson());

            $devices = $user->devices->toArray();
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $user->devices->count();
            $res['error'] = 0;
            $res['devices'] = $devices;
            return json_encode($res);

        }else{
            Log::error('[DEVICE] [INDEX] user is not login');
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
            Log::info('[DEVICE] [ADD] user info: '.$user->toJson());

            $name = $request->input('name');
            $type = $request->input('type');
            $room_id = $request->input('room_id');
            $brand = $request->input('brand');
            $model = $request->input('model');
            $imei = $request->input('imei');
            $nodeID = $request->input('nodeID');
            $address = $request->input('address');
            $bInfrared = $request->input('infrared') == 'true';

            Log::info('[DEVICE] [ADD] infrared value: '.$request->input('infrared').'type: '.$type.'name: '.$name);

            $device = new Device([
                'name' => $name,
                'type' => $type,
                'room_id' => $room_id,
                'brand' => $brand,
                'model' => $model,
                'imei' => $imei,
                'nodeID' => $nodeID,
                'address' => $address,
                'infrared' => $bInfrared,
                'status' => 0,
            ]);

            $user->devices()->save($device);

            $res = $device->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[DEVICE] [ADD] user is not login');
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
            Log::info('[DEVICE] [QUERY] user info: '.$user->toJson());

            $device = $user->devices()->find($id);
            if(is_null($device)){
                Log::error('[DEVICE] [QUERY] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Device::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[DEVICE] [QUERY] user is not login');
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

    public function action(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[DEVICE] [ACTION] user info: '.$user->toJson());

            $devices = $request->input('devices');
            if(empty($devices)){
                Log::error('[DEVICE] [ACTION] missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [devices]'));
            }

            $params = array();
            $params['type'] = 100;
            $params['devices'] = $devices;
            DeviceCommand::sendMessage($user->id, $params, false, true);

            return json_encode(array('error'=>0));
            //foreach(explode(',', $devices) as $id_action){
            //    $arr = array();
            //    parse_str($id_action, $arr);
            //    foreach($arr as $deviceID=>$action){
            //        // TODO perform operations on a certain device
            //    }
            //}
        }else{
            Log::error('[DEVICE] [ACTION] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function discover(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[DEVICE] [DISCOVER] user info: '.$user->toJson());

            $imei = $request->input('imei');
            if(empty($imei)){
                Log::error('[DEVICE] [DISCOVER] missing parameter [imei]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [imei]'));
            }

            $nodeID = $request->input('nodeID');
            if(empty($nodeID)){
                Log::error('[DEVICE] [DISCOVER] missing parameter [nodeID]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [nodeID]'));
            }

            $nodeType = $request->input('nodeType');
            if(empty($nodeType)){
                Log::error('[DEVICE] [DISCOVER] missing parameter [nodeType]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [nodeType]'));
            }
            Log::info('[DEVICE] [DISCOVER] nodeID['.$nodeID.'] imei['.$imei.'] nodeType['.$nodeType.']');

            $params = array();
            $params['type'] = 101;
            $params['imei'] = $imei;
            $params['nodeID'] = $nodeID;
            $params['nodeType'] = $nodeType;

            DeviceCommand::sendMessage($user->id, $params, true, false);

            return json_encode(array('error'=>0));
            //foreach(explode(',', $devices) as $id_action){
            //    $arr = array();
            //    parse_str($id_action, $arr);
            //    foreach($arr as $deviceID=>$action){
            //        // TODO perform operations on a certain device
            //    }
           //}
        }else{
            Log::error('[DEVICE] [DISCOVER] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function status(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[DEVICE] [STATUS] user info: '.$user->toJson());

            $devices = $request->input('devices');
            if(empty($devices)){
                Log::error('[DEVICE] [STATUS] missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [devices]'));
            }

            $params = array();
            $params['type'] = 202;
            $params['devices'] = $devices;

            DeviceCommand::sendMessage($user->id, $params, true, false);

            return json_encode(array('error'=>0));
        }else{
            Log::error('[DEVICE] [STATUS] user is not login');
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
            Log::info('[DEVICE] [UPDATE] user info: '.$user->toJson());

            $device = $user->devices()->find($id);
            if(is_null($device)){
                Log::error('[DEVICE] [UPDATE] uid:'.$user->id.' no such item:'.$id);
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

            $nodeID = $request->input("nodeID");
            if(!empty($nodeID)){
                 $device->nodeID = $nodeID;
            }

            $address = $request->input("address");
            if(!empty($address)){
                 $device->address = $address;
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
            Log::error('[DEVICE] [UPDATE] user is not login');
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
            Log::info('[DEVICE] [DELETE] user info: '.$user->toJson());

            $device = $user->devices()->find($id);
            if(is_null($device)){
                Log::error('[DEVICE] [DELETE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Device::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('[DEVICE] [DELETE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
