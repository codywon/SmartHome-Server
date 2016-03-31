<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Trigger;
use smarthome\User;
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

            $total = 0;
            $devices = null;
            if(empty($user->group)){
                $devices = $user->devices->toArray();
                $total = $user->devices->count();
            }else{
                $devices = Device::where('group', $user->group)->get()->toArray();
                $total = Device::where('group', $user->group)->count();
            }
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $total;
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
            $status = $request->input('status');
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
                'status' => $status,
                'group' => $user->group,
            ]);

            try{
                $user->devices()->save($device);
            }catch(Exception $e){

            }

            $params = array();
            $params['type'] = 103;
            DeviceCommand::sendMessage($user->id, $params, false, true);

            ob_end_clean();

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

            $device = null;
            if(empty($user->group)){
                $device = $user->devices()->find($id);
            }else{
                $device = Device::where('group', $user->group)->get()->find($id);
            }
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
            Log::info('operate device, devices: '.$devices);

            foreach(explode(',', $devices) as $id_action){
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
            $params['type'] = 100;
            $params['devices'] = $devices;
            if(empty($user->group) || $user->role == 1){
                DeviceCommand::sendMessage($user->id, $params, false, true);
            }else{
                $groupAdmin = User::where('group', '=', $user->group)->where('role', '=', 1)->first();
                DeviceCommand::sendMessage($groupAdmin->id, $params, false, true);
            }
            ob_end_clean();

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

            $status = $request->input('status');
            if(empty($status)){
                Log::error('discover device, missing parameter [status]');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [status]'));
            }
            Log::info('discover device, nodeID['.$nodeID.'] imei['.$imei.'] nodeType['.$nodeType.'] index['.$index.'] status['.$status.']');

            $device = $imei.':'.$nodeID.':'.$index.':'.$nodeType.':'.$status;

            $key = $user->id;
            if(!empty($user->group)){
                $key = $user->group;
            }
            SearchDevice::add($key, $device);
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

            $deviceKey = array();
            $devices = array();
            $startTime = time();
            while(time() - $startTime <= 3){

                // TODO check response from contoller
                $key = $user->id;
                if(!empty($user->group)){
                     $key = $user->group;
                }
                $values = SearchDevice::get($key);
                if(count($values) == 0){
                    sleep(1);
                    continue;
                }

                foreach($values as $value){
                    $deviceInfos = explode(":", $value);

                    $key = $deviceInfos[0].$deviceInfos[1].$deviceInfos[2].$deviceInfos[3];
                    if(in_array($key, $deviceKey)){
                         continue;
                    }

                    $device = array();
                    $device['imei'] = $deviceInfos[0];
                    $device['nodeID'] = $deviceInfos[1];
                    $device['index'] = $deviceInfos[2];
                    $device['nodeType'] = $deviceInfos[3];
                    $device['status'] = $deviceInfos[4];

                    array_push($devices, $device);
                    array_push($deviceKey, $key);
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

            foreach(explode(',', $devices) as $id_action){
                $arr = array();
                parse_str($id_action, $arr);
                foreach($arr as $deviceID=>$action){
                    // TODO perform operations on a certain device
                    $device = Device::find($deviceID);
                    if(!is_null($device)){
                        $device->status = $action;
                        $device->save();

                        // check trigger rules
                        $triggers = Trigger::where('condition_device', '=', $device->id)->where('condition_action', '=', $action)->get();
                        if(!is_null($triggers)){
                            foreach ($triggers as $trigger) {
                                $params = array();
                                $params['type'] = 100;
                                $params['devices'] = $trigger->trigger_device.'='.$trigger->trigger_action;
                                if(empty($user->group) || $user->role == 1){
                                    DeviceCommand::sendMessage($user->id, $params, false, true);
                                }else{
                                    $groupAdmin = User::where('group', '=', $user->group)->where('role', '=', 1)->first();
                                    DeviceCommand::sendMessage($groupAdmin->id, $params, false, true);
                                }
                                ob_end_clean();
                            }
                        }
                    }
                }
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

            $device = null;
            if(empty($user->group)){
                $device = $user->devices()->find($id);
            }else{
                $device = Device::where('group', $user->group)->get()->find($id);
            }
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

//            $device = null;
//            if(empty($user->group)){
//                $device = $user->devices()->find($id);
//            }else{
//                $device = Device::where('group', $user->group)->get()->find($id);
//            }
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
