<?php

namespace smarthome\Http\Controllers;

use Log;
use Auth;
use Illuminate\Http\Request;

use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

use smarthome\Message;

class ApiMessageController extends Controller
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

            $res = $user->messages->toArray();
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res['total'] = $user->rooms->count();
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

            $message = $user->messages()->find($id);
            if(is_null($message)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $res = Message::find($id)->toArray();
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

            $message = $user->messages()->find($id);
            if(is_null($message)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            $read = $request->input("read");
            if(!empty($read)){
                 $message->read = $read;
            }

            $message->save();

            $res = $message->toArray();
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

            $message = $user->messages()->find($id);
            if(is_null($message)){
                return json_encode(array('error'=>104, 'reason'=>'no such item'));
            }

            Message::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            return json_encode(array('error'=>100, 'reason'=>'user is not login'));
        }
    }
}
