<?php

namespace smarthome\Http\Controllers;

use Illuminate\Http\Request;

use Log;
use Auth;

use smarthome\Device;
use smarthome\User;
use smarthome\Trigger;
use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

class ApiTriggerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('get all triggers, uid: '.$user->id);

            $total = 0;
            $triggers = null;
            if(empty($user->group)){
                $triggers = $user->triggers->toArray();
                $total = $user->triggers()->count();
            }else{
                $triggers = Trigger::where('group', $user->group)->get()->toArray();
                $total = Trigger::where('group', $user->group)->count();
            }
            //foreach($user->devices() as $device){
            //    Log::info($device->toArray());
            //    array_push($res, $device->toArray());
            //}
            $res = array();
            $res['total'] = $total;
            $res['error'] = 0;
            $res['triggers'] = $triggers;
            return json_encode($res);

        }else{
            Log::error('get all triggers failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('add trigger, uid: '.$user->id);

            $condition_device = $request->input('condition_device');
            $condition_action = $request->input('condition_action');
            $trigger_device = $request->input('trigger_device');
            $trigger_action = $request->input('trigger_action');

            if(empty($condition_device) || empty($condition_action) || empty($trigger_device) || empty($trigger_action)){
                Log::error('add trigger failed, miss parameter');
                return json_encode(array('error'=>601, 'reason'=>'添加触发模式失败，缺少参数'));
            }

            $count = Trigger::where('condition_device', '=', $condition_device)->where('condition_action', '=', $condition_action)
                ->where('trigger_device', '=', $trigger_device)->where('trigger_action', '=', $trigger_action)->count();
            if($count != 0){
                Log::error('add trigger failed, the same trigger was added');
                return json_encode(array('error'=>602, 'reason'=>'添加触发模式失败，已添加相同的触发模式'));
            }

            Log::info('add trigger, condition:'.$condition_device.'='.$condition_action.', trigger:'.$trigger_device.'='.$trigger_action);

            $trigger = new Trigger([
                'condition_device' => $condition_device,
                'condition_action' => $condition_action,
                'trigger_device' => $trigger_device,
                'trigger_action' => $trigger_action,
                'group' => $user->group,
            ]);

            try{
                $user->triggers()->save($trigger);
            }catch(Exception $e){

            }

            $res = $trigger->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('add trigger failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('query trigger, uid: '.$user->id);

            $trigger = null;
            if(empty($user->group)){
                $trigger = $user->triggers()->find($id);
            }else{
                $trigger = Trigger::where('group', $user->group)->get()->find($id);
            }
            if(is_null($trigger)){
                Log::error('query trigger failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应条目'));
            }

            $res = Trigger::find($id)->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('query trigger failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('update trigger, uid: '.$user->id);

            $condition_device = $request->input('condition_device');
            $condition_action = $request->input('condition_action');
            $trigger_device = $request->input('trigger_device');
            $trigger_action = $request->input('trigger_action');

            if(empty($condition_device) || empty($condition_action) || empty($trigger_device) || empty($trigger_action)){
                Log::error('update trigger failed, miss parameter');
                return json_encode(array('error'=>601, 'reason'=>'更新触发模式失败，缺少参数'));
            }

            $trigger = null;
            if(empty($user->group)){
                $trigger = $user->triggers()->find($id);
            }else{
                $trigger = Trigger::where('group', $user->group)->get()->find($id);
            }
            if(is_null($trigger)){
                Log::error('update trigger failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应设备'));
            }

            $trigger->$condition_device = $condition_device;
            $trigger->$condition_action = $condition_action;
            $trigger->$trigger_device = $trigger_device;
            $trigger->$trigger_action = $trigger_action;
            $trigger->save();

            Log::info('update trigger, condition:'.$condition_device.'='.$condition_action.', trigger:'.$trigger_device.'='.$trigger_action);

            $res = $trigger->toArray();
            $res['error'] = 0;
            return json_encode($res);
        }else{
            Log::error('update trigger failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::check()){
            $user = Auth::user();
            Log::info('delete trigger, uid: '.$user->id);

            $trigger = null;
            if(empty($user->group)){
                $trigger = $user->triggers()->find($id);
            }else{
                $trigger = Trigger::where('group', $user->group)->get()->find($id);
            }
            if(is_null($trigger)){
                Log::error('delete trigger failed, uid:'.$user->id.' no such item:'.$id);
                return json_encode(array('error'=>104, 'reason'=>'未找到相应条目'));
            }

            Trigger::destroy($id);

            return json_encode(array('error'=> 0));
        }else{
            Log::error('delete trigger failed, user is not login');
            return json_encode(array('error'=>100, 'reason'=>'用户未登陆'));
        }
    }
}
