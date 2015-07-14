<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }
}
