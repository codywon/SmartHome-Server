<?php
/**
 * Created by PhpStorm.
 * User: zhiqiang
 * Date: 3/6/2016
 * Time: 13:48
 */

namespace smarthome;

use Log;
use Redis;

class SceneLRUByGroup
{
    const LRU_KEY_PREFIX = "scene-lru";

    public static function incr($gid, $scene_id)
    {
        $sceneLRUKey = self::LRU_KEY_PREFIX.$gid;
        Redis::command('zincrby', [$sceneLRUKey, 1, $scene_id]);
    }

    public static function getFirstSixScene($gid)
    {
        $sceneLRUKey = self::LRU_KEY_PREFIX.$gid;
        $values = Redis::command('zrevrange', [$sceneLRUKey, 0, 5]);
        return $values;
    }
}