<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $fillable = ['name', 'room_id', 'type', 'index', 'infrared', 'brand', 'model', 'imei', 'nodeID', 'address', 'status', 'group'];

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }

    public function room()
    {
        return $this->belongsTo('smarthome\Room');
    }

    public function properties()
    {
        return $this->hasMany('smarthome\DeviceProperty');
    }
}
