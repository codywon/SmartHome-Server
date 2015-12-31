<?php

namespace smarthome\Http\Controllers;

use Illuminate\Http\Request;

use Log;
use smarthome\SMS;
use smarthome\Http\Requests;
use smarthome\Http\Controllers\Controller;

class SMSController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function apply(Request $request)
    {
        $phone = $request['phone'];
        if(empty($phone)){
            Log::error("miss param [phone]");
            return json_encode(array('error'=>120));
        }

        Log::info('phone['.$phone.'] apply verify code');
        if(!SMS::isEnableSendAnotherCode($phone)){
            return json_encode(array('error'=>122));
        }

        if(SMS::sendSMSVerifyCode($phone)){
             return json_encode(array('error'=>0));
        }

        return json_encode(array('error'=>121));
    }

    public function verify(Request $request)
    {
        $phone = $request['phone'];
        if(empty($phone)){
            Log::error("miss param [phone]");
            return json_encode(array('error'=>120));
        }

        $code = $request['code'];
        if(empty($code)){
            Log::error("miss param [code]");
            return json_encode(array('error'=>120));
        }

        if(SMS::validateSMSCode($phone, $code)){
            return json_encode(array('error'=>0));
        }

        return json_encode(array('error'=>123));
    }
}

