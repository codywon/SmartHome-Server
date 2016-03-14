<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Room;

class ApiRoomController extends Controller
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
            Log::info('get all rooms, uid: '.$user->id);

            $total = 0;
            $rooms = null;
            if(empty($user->group)){
                $rooms = $user->rooms->toArray();
                $total = $user->rooms->count();
            }else{
                $rooms = Room::where('group', $user->group)->get()->toArray();
                $total = Room::where('group', $user->group)->count();
            }

            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $total;
            $res['error'] = 0;
            $res['rooms'] = $rooms;
            return json_encode($res);

        }else{
            Log::error('get all rooms failed, user is not login');
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
            Log::info('create room, uid: '.$user->id);

            $name = $request->input('name');
            $floor = $request->input('floor');
            $type = $request->input('type');

            $room = new Room([
                'name' => $name,
                'floor' => $floor,
                'type' => $type,
                'group' => $user->group,
            ]);

            $user->rooms()->save($room);

            $res = $room->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('create room failed, user is not login');
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
            Log::info('query room, uid: '.$user->id);

            $room = null;
            if(empty($user->group)){
                $room = $user->rooms()->find($id);
            }else{
                $room = Room::where('group', $user->group)->get()->find($id);
            }

            if(is_null($room)){
                Log::error('query room, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应房间'));
            }

            $res = $room->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query room failed, user is not login');
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

    /*
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
            Log::info('update room, uid: '.$user->id);

            $room = null;
            if(empty($user->group)){
                $room = $user->rooms()->find($id);
            }else{
                $room = Room::where('group', $user->group)->get()->find($id);
            }

            if(is_null($room)){
                Log::error('update room, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应房间'));
            }

            $name = $request->input("name");
            if(!empty($name)){
                 $room->name = $name;
            }

            $floor = $request->input("floor");
            if(!empty($floor)){
                 $room->floor = $floor;
            }

            $room->save();

            $res = $room->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('update room failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function getDevice($id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('get all devices in room, uid: '.$user->id);

            $room = null;
            if(empty($user->group)){
                $room = $user->rooms()->find($id);
            }else{
                $room = Room::where('group', $user->group)->get()->find($id);
            }

            if(is_null($room)){
                Log::error('get all devices in room, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应房间'));
            }

            $devices = $room->devices->toArray();
            $res = array();
            $res['devices'] = $devices;
            $res['error'] = 0;

            return json_encode($res);
        } else{
            Log::error('get all devices in room failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    public function addDevice(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('add device to room, uid: '.$user->id);

            $room = null;
            if(empty($user->group)){
                $room = $user->rooms()->find($id);
            }else{
                $room = Room::where('group', $user->group)->get()->find($id);
            }

            if(is_null($room)){
                Log::error('add device to room, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应房间'));
            }

            $deviceIDs = $request->input('devices'); // devices:id1,id2,id3 ...
            if(empty($deviceIDs)){
                Log::error('add device to room, missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'缺少参数 [devices]'));
            }

            foreach(explode(',', $deviceIDs) as $deviceID){
                $device = Device::find($deviceID);
                if(is_null($device)){
                    Log::error('add device to room, uid:'.$user->id.' no such item:'.$deviceID);
                    continue;
                }
                $device->room_id = $id;
                $device->save();
            }

            $devices = Room::find($id)->devices->toArray();
            $res = array();
            $res['devices'] = $devices;
            $res['error'] = 0;

            return json_encode($res);
        } else{
            Log::error('add device to room failed, user is not login');
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
            Log::info('delete room, uid: '.$user->id);

            $room = null;
            if(empty($user->group)){
                $room = $user->rooms()->find($id);
            }else{
                $room = Room::where('group', $user->group)->get()->find($id);
            }
            if(is_null($room)){
                Log::error('delete room, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应房间'));
            }

            Room::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('delete room failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
