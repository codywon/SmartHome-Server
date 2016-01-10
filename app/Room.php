<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = ['name', 'room_id', 'floor', 'type', 'group'];

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }

    public function devices()
    {
         return $this->hasMany('smarthome\Device');
    }
}
