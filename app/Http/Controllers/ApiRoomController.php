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
            Log::info('[ROOM] [INDEX] user info: '.$user->toJson());

            $rooms = $user->rooms->toArray();
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $user->rooms->count();
            $res['error'] = 0;
            $res['rooms'] = $rooms;
            return json_encode($res);

        }else{
            Log::error('[ROOM] [INDEX] user is not login');
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
            Log::info('[ROOM] [ADD] user info: '.$user->toJson());

            $name = $request->input('name');
            $floor = $request->input('floor');

            $room = new Room([
                'name' => $name,
                'floor' => $floor,
            ]);

            $user->rooms()->save($room);

            $res = $room->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[ROOM] [ADD] user is not login');
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
            Log::info('[ROOM] [QUERY] user info: '.$user->toJson());

            $room = $user->rooms()->find($id);
            if(is_null($room)){
                Log::error('[ROOM] [QUERY] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Room::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('[ROOM] [QUERY] user is not login');
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
            Log::info('[ROOM] [UPDATE] user info: '.$user->toJson());

            $room = $user->rooms()->find($id);
            if(is_null($room)){
                Log::error('[ROOM] [UPDATE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
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
            Log::error('[ROOM] [UPDATE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function getDevice($id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[ROOM] [DEVICE] user info: '.$user->toJson());

            $room = $user->rooms()->find($id);
            if(is_null($room)){
                Log::error('[ROOM] [DEVICE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $devices = Room::find($id)->devices->toArray();
            $res = array();
            $res['devices'] = $devices;
            $res['error'] = 0;

            return json_encode($res);
        } else{
            Log::error('[ROOM] [DEVICE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }

    public function addDevice(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('[ROOM] [DEVICE] user info: '.$user->toJson());

            $room = $user->rooms()->find($id);
            if(is_null($room)){
                Log::error('[ROOM] [DEVICE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $deviceIDs = $request->input('devices'); // devices:id1,id2,id3 ...
            if(empty($deviceIDs)){
                Log::error('[ROOM] [DEVICE] missing parameter [devices]');
                return json_encode(array('error'=>201, 'reason'=>'missing parameter [devices]'));
            }

            foreach(explode(',', $deviceIDs) as $deviceID){
                $device = Device::find($deviceID);
                if(is_null($device)){
                    Log::error('[ROOM] [DEVICE] uid:'.$user->id.' no such item:'.$deviceID);
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
            Log::error('[ROOM] [DEVICE] user is not login');
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
            Log::info('[ROOM] [DELETE] user info: '.$user->toJson());

            $room = $user->rooms()->find($id);
            if(is_null($room)){
                Log::error('[ROOM] [DELETE] uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Room::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('[ROOM] [DELETE] user is not login');
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
