<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $fillable = ['name', 'room_id', 'type', 'infrared', 'status'];

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

    public function scenes()
    {
        return $this->belongsToMany('smarthome\Scene');
    }
}
