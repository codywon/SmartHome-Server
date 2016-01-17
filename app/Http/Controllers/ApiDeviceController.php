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
            Log::info('get all devices, uid: '.$user->id);

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
            Log::error('get all devices failed, user is not login');
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
            Log::info('add device, uid: '.$user->id);

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
                return json_encode(array('error'=>301, 'reason'=>'该设备已经被添加'));
            }

            $count = Device::where('user_id', '=', $user->id)->where('name', '=', $name)->count();
            if($count != 0){
                Log::error('add device failed, the same name was added');
                return json_encode(array('error'=>302, 'reason'=>'该用户已经有相同名称的设备'));
            }

            Log::info('add device, infrared value: '.$request->input('infrared').'type: '.$type.'name: '.$name);

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
            Log::error('add device failed, user is not login');
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
            Log::info('query device, uid: '.$user->id);

            $device = $user->devices()->find($id);
            if(is_null($device)){
                Log::error('query device failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应设备'));
            }

            $res = Device::find($id)->toArray();
            $res['error'] = 0;
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
            Log::info('operate device, uid: '.$user->id);

            $devices = $request->input('devices');
            if(empty($devices)){
                Log::error('operate device failed, missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [devices]'));
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
            Log::error('operate device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [imei]'));
            }

            $nodeID = $request->input('nodeID');
            if(empty($nodeID)){
                Log::error('discover device, missing parameter [nodeID]');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [nodeID]'));
            }

            $index = $request->input('index');
            if(empty($index)){
                Log::error('discover device, missing parameter [index]'.$index);
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [index]'));
            }

            $nodeType = $request->input('nodeType');
            if(empty($nodeType)){
                Log::error('discover device, missing parameter [nodeType]');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [nodeType]'));
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
            Log::error('discover device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
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
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function status(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('query device status, user info: '.$user->toJson());

            $devices = $request->input('devices');
            if(empty($devices)){
                Log::error('query device status failed, missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [devices]'));
            }

            $params = array();
            $params['type'] = 202;
            $params['devices'] = $devices;

            DeviceCommand::sendMessage($user->id, $params, true, false);

            return json_encode(array('error'=>0));
        }else{
            Log::error('query device status failed, user is not login');
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
            Log::info('update deivce, uid: '.$user->id);

            $device = $user->devices()->find($id);
            if(is_null($device)){
                Log::error('update device failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应设备'));
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
            Log::error('update device failed, user is not login');
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
            Log::info('delete device, uid: '.$user->id);

            $device = $user->devices()->find($id);
            if(is_null($device)){
                Log::error('delete device failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应设备'));
            }

            Device::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('delete device failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
