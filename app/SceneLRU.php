<?php

namespace smarthome;

use Log;
use Redis;

class SceneLRU
{
    const LRU_KEY_PREFIX = "scene-lru";

    public static function incr($uid, $scene_id)
    {
        $sceneLRUKey = self::LRU_KEY_PREFIX.$uid;
        Redis::command('zincrby', [$sceneLRUKey, 1, $scene_id]);
    }

    public static function getFirstSixScene($uid)
    {
        $sceneLRUKey = self::LRU_KEY_PREFIX.$uid;
        $values = Redis::command('zrevrange', [$sceneLRUKey, 0, 5]);
        return $values;
    }
}

