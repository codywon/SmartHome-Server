<?php
/**
 * Created by PhpStorm.
 * User: zhiqiang
 * Date: 3/6/2016
 * Time: 16:19
 */

namespace smarthome;

use Log;
use Redis;

class Security
{
    public static function writeVerifyResultToRedis($phone, $bSuccess)
    {
        $key = 'security_'.$phone.':isChecked';
        $timeout = 1 * 60;
        $value = $bSuccess ? 1 : 0;

        Redis::command('set', [$key, $value, 'EX', $timeout]);
    }

    public static function isChecked($phone)
    {
        $key = 'security_'.$phone.':isChecked';
        return Redis::get($key) == 1;
    }
}