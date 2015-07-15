<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class DeviceProperty extends Model
{
    protected $table = 'device_property';

    public function device()
    {
        return $this->belongsTo('smarthome\Device');
    }
}
