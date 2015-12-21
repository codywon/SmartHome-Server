<?php

namespace smarthome;

use Log;
use Redis;
use GuzzleHttp\Client;

class SMS
{
    const SMS_API_KEY = "ffb3b34f77d7a874f103e7035a35012c";
    const SMS_API_URL = "http://yunpian.com/v1/sms/send.json";
    const SMS_TEMPLATE = "【华郡科技】您的验证码是";

    public static function generateVerifyCode()
    {
        return rand(100000, 999999);
    }

    public static function writeVerifyCodeToRedis($phone, $code)
    {
        $verifyKey   = $phone.':VerifyCode';
        $intervalKey = $phone.':Interval';
        $timeout     = 30 * 60;     // 30 minutes
        $interval    = 120;         // could send another code after 120 second later

        Redis::command('set', [$verifyKey, $code, 'EX', $timeout]);
        Redis::command('set', [$intervalKey, 1, 'EX', $interval]);
    }

    public static function writeVerifyResultToRedis($phone, $bSuccess)
    {
        $key = $phone.':isChecked';
        $timeout = 30 * 60;
        $value = $bSuccess ? 1 : 0;

        Redis::command('set', [$key, $value, 'EX', $timeout]);
    }

    public static function isChecked($phone)
    {
        $key = $phone.':isChecked';
        return Redis::get($key) == 1;
    }

    public static function isEnableSendAnotherCode($phone)
    {
        $verifyKey   = $phone.':VerifyCode';
        $intervalKey = $phone.':Interval';

        return Redis::get($intervalKey) != 1;
    }

    public static function validateSMSCode($phone, $code)
    {
        $verifyKey = $phone.':VerifyCode';
        $value = Redis::get($verifyKey);

        $res = ($value == $code);
        SMS::writeVerifyResultToRedis($phone, $res);
        return $res;
    }

    public static function sendSMSVerifyCode($phone)
    {
        $code = SMS::generateVerifyCode();
        $text = self::SMS_TEMPLATE.$code;

        $data='apikey='.self::SMS_API_KEY.'&text='.urlencode($text).'&mobile='.urlencode($phone);

        $client = new Client();
        $response = $client->request('POST', self::SMS_API_URL, [
            'body' => $data
        ]);

        $status = $response->getStatusCode();
        if($status == 200){
            $body = $response->getBody();
            Log::info('response data:'.$body);
            $result = json_decode($body);

            if($result->{'code'} == 0){
                // write code to redis
                SMS::writeVerifyCodeToRedis($phone, $code);
                return true;
            }

            Log::error('send sms failed, phone['.$phone.'] error['.$result->{'code'}.'] message['.$result->{'msg'}.'] detail['.$result->{'detail'}.']');
        }else{
            // send sms failed
            Log::error('send sms failed, phone['.$phone.'] StatusCode['.$status.']');
        }
        return false;
    }
}

