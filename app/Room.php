<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }
}
