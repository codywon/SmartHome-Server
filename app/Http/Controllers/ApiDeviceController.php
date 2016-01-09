<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Device;
use smarthome\DeviceCommand;
use smarthome\SearchDevice;

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
            $index = $request->input('index');
            $room_id = $request->input('room_id');
            $brand = $request->input('brand');
            $model = $request->input('model');
            $imei = $request->input('imei');
            $nodeID = $request->input('nodeID');
            $address = $request->input('address');
            $bInfrared = $request->input('infrared') == 'true';

            if(empty($index)){
                $index = -1;
            }

            $count = Device::where('imei', '=', $imei)->where('index', '=', $index)->count();
            if($count != 0){
                Log::error('add device failed, has already added');
                return json_encode(array('error'=>301, 'reason'=>'this device had already added'));
            }

            $count = Device::where('user_id', '=', $user->id)->where('name', '=', $name)->count();
            if($count != 0){
                Log::error('add device failed, the same name was added');
                return json_encode(array('error'=>302, 'reason'=>'the same name was added'));
            }

            Log::info('[DEVICE] [ADD] infrared value: '.$request->input('infrared').'type: '.$type.'name: '.$name);

            $device = new Device([
                'name' => $name,
                'type' => $type,
                'index' => $index,
                'room_id' => $room_id,
                'brand' => $brand,
                'model' => $model,
                'imei' => $imei,
                'nodeID' => $nodeID,
                'address' => $address,
                'infrared' => $bInfrared,
                'status' => 0,
            ]);

            try{
                $user->devices()->save($device);
            }catch(Exception $e){

            }

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
            Log::info('discover device, user info: '.$user->toJson());

            $imei = $request->input('imei');
            if(empty($imei)){
                Log::error('discover device, missing parameter [imei]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [imei]'));
            }

            $nodeID = $request->input('nodeID');
            if(empty($nodeID)){
                Log::error('discover device, missing parameter [nodeID]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [nodeID]'));
            }

            $index = $request->input('index');
            if(empty($index)){
                Log::error('discover device, missing parameter [index]'.$index);
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [index]'));
            }

            $nodeType = $request->input('nodeType');
            if(empty($nodeType)){
                Log::error('discover device, missing parameter [nodeType]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [nodeType]'));
            }
            Log::info('discover device, nodeID['.$nodeID.'] imei['.$imei.'] nodeType['.$nodeType.'] index['.$index.']');

            $device = $imei.':'.$nodeID.':'.$index.':'.$nodeType;
            SearchDevice::add($user->id, $device);
            //$params = array();
            //$params['type'] = 101;
            //$params['imei'] = $imei;
            //$params['nodeID'] = $nodeID;
            //$params['index'] = $index;
            //$params['nodeType'] = $nodeType;

            //DeviceCommand::sendMessage($user->id, $params, true, false);

            return json_encode(array('error'=>0));
            //foreach(explode(',', $devices) as $id_action){
            //    $arr = array();
            //    parse_str($id_action, $arr);
            //    foreach($arr as $deviceID=>$action){
            //        // TODO perform operations on a certain device
            //    }
           //}
        }else{
            Log::error('discover device, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function search(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('search device, uid: '.$user->id);

            $params = array();
            $params['type'] = 102;

            DeviceCommand::sendMessage($user->id, $params, false, true);

            $devices = array();
            $startTime = time();
            while(time() - $startTime <= 5){

                // TODO check response from contoller
                $values = SearchDevice::get($user->id);
                if(count($values) == 0){
                    sleep(1);
                    continue;
                }

                foreach($values as $value){
                    $deviceInfos = explode(":", $value);
                    $device = array();
                    $device['imei'] = $deviceInfos[0];
                    $device['nodeID'] = $deviceInfos[1];
                    $device['index'] = $deviceInfos[2];
                    $device['nodeType'] = $deviceInfos[3];

                    array_push($devices, $device);
                }
            }

            // what a fucking bug !
            ob_end_clean();

            $res = array();
            $res['devices'] = $devices;
            $res['error'] = 0;

            return json_encode($res);

        }else{
            Log::error('search device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function status(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('query device status, user info: '.$user->toJson());

            $devices = $request->input('devices');
            if(empty($devices)){
                Log::error('query device status, missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [devices]'));
            }

            $params = array();
            $params['type'] = 202;
            $params['devices'] = $devices;

            DeviceCommand::sendMessage($user->id, $params, true, false);

            return json_encode(array('error'=>0));
        }else{
            Log::error('query device status, user is not login');
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
