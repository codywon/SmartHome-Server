<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }
}
