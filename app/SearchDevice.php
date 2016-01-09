<?php

namespace smarthome;

use Log;
use Redis;

class SearchDevice
{
    const SEARCH_DEVICE_PREFIX = "search-device";

    public static function add($uid, $device)
    {
        $key = self::SEARCH_DEVICE_PREFIX.$uid;
        Redis::command('sadd', [$key, $device]);
    }

    public static function get($uid)
    {
        $key = self::SEARCH_DEVICE_PREFIX.$uid;
        $devices = array();
        while(true)
        {
            $device = Redis::command('spop', [$key]);
            if(!is_null($device)){
                array_push($devices, $device);
                Log::info('get device from redis:'.$device);
            }else{
                Log::info('no device find, break');
                break;
            }
        }
        return $devices;
    }
}
