<?php

namespace smarthome;

use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

use Log;

class DeviceCommand
{
    const JPUSH_APP_KEY = 'a125f9c14f03647b505a279d';
    const JPUSH_MASTER_SECRET = 'e62b1d069e6bb0a4185130a1';

    public static function sendMessage($uid, $params, $bMobile = true, $bCenterController = false)
    {
        $client = new JPushClient(self::JPUSH_APP_KEY, self::JPUSH_MASTER_SECRET);
        $alias = array($uid);
        $tags = array();
        $index = 0;
        if($bMobile){
            $tags[$index++] = "mobile";
        }
        if($bCenterController){
            $tags[$index++] = "center_controller";
        }

        try {
            $result = $client->push()
                ->setPlatform(M\all)
                ->setAudience(M\audience(M\tag($tags), M\alias($alias)))
 //               ->setNotification(M\notification('Hi, JPush'))
                ->setMessage(M\message('msg content', null, null, $params))
                ->printJSON()
                ->send();

            Log::info('[DeviceCommand]  send device command success, sendno['.$result->sendno.'] msg_id['.$result->msg_id.'] response json['.$result->json.']');

        } catch (APIRequestException $e) {
            Log::error('[DeviceCommand] push failed. http code['.$e->httpCode.'] code['.$e->code.'] message['.$e->message.'] response json['.$e->json.']
                rateLimitLimit['.$e->rateLimitLimit.'] rateLimitRemaining['.$e->rateLimitRemaining.'] rateLimitReset['.$e->rateLimitReset.']');

        } catch (APIConnectionException $e) {
            Log::error('[DeviceCommand] push failed. message['.$e->getMessage().'] IsResponseTimeout['.$e->isResponseTimeout.']');
            //response timeout means your request has probably be received by JPUsh Server,please check that whether need to be pushed again.
        }
    }

    public static function sendNotification($uid, $notice, $bMobile = true, $bCenterController = false)
    {
        $client = new JPushClient(self::JPUSH_APP_KEY, self::JPUSH_MASTER_SECRET);
        $alias = array($uid);
        $tags = array();
        $index = 0;
        if($bMobile){
            $tags[$index++] = "mobile";
        }
        if($bCenterController){
            $tags[$index++] = "center_controller";
        }

        try {
            $result = $client->push()
                ->setPlatform(M\all)
                ->setAudience(M\audience(M\tag($tags), M\alias($alias)))
                ->setNotification(M\notification($notice))
                ->printJSON()
                ->send();

            Log::info('[DeviceCommand]  send device command success, sendno['.$result->sendno.'] msg_id['.$result->msg_id.'] response json['.$result->json.']');

        } catch (APIRequestException $e) {
            Log::error('[DeviceCommand] push failed. http code['.$e->httpCode.'] code['.$e->code.'] message['.$e->message.'] response json['.$e->json.']
                rateLimitLimit['.$e->rateLimitLimit.'] rateLimitRemaining['.$e->rateLimitRemaining.'] rateLimitReset['.$e->rateLimitReset.']');

        } catch (APIConnectionException $e) {
            Log::error('[DeviceCommand] push failed. message['.$e->getMessage().'] IsResponseTimeout['.$e->isResponseTimeout.']');
            //response timeout means your request has probably be received by JPUsh Server,please check that whether need to be pushed again.
        }
    }
}
