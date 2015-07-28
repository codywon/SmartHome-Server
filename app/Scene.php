<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $table = 'scenes';

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }

    public function devices()
    {
        return $this->belongsToMany('smarthome\Device');
    }
}
