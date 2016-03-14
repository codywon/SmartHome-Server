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
            Log::info('get all messages, uid: '.$user->id);

            $total = 0;
            $messages = null;
            if(empty($user->group)){
                $messages = $user->messages->toArray();
                $total = $user->messages->count();
            }else{
                $messages = Message::where('group', $user->group)->get()->toArray();
                $total = Message::where('group', $user->group)->count();
            }

            $messages = $user->messages->toArray();
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $total;
            $res['error'] = 0;
            $res['messages'] = $messages;
            return json_encode($res);

        }else{
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
            Log::info('query message, user info: '.$user->toJson());

            $message = null;
            if(empty($user->group)){
                $message = $user->message()->find($id);
            }else{
                $message = Message::where('group', $user->group)->get()->find($id);
            }

            if(is_null($message)){
                Log::error('query message failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相关消息'));
            }

            $res = Message::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query message failed, user is not login');
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
            Log::info('update message, uid: '.$user->id);

            $message = null;
            if(empty($user->group)){
                $message = $user->message()->find($id);
            }else{
                $message = Message::where('group', $user->group)->get()->find($id);
            }

            if(is_null($message)){
                Log::error('update message failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'用户未登陆'));
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
            Log::error('update message failed, user is not login');
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
            Log::info('delete message, uid: '.$user->id);

            $message = null;
            if(empty($user->group)){
                $message = $user->$message()->find($id);
            }else{
                $message = Message::where('group', $user->group)->get()->find($id);
            }
            if(is_null($message)){
                Log::error('delete message failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相关消息'));
            }

            Message::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('delete message failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
