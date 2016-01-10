<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = ['user_id', 'level', 'from', 'title', 'content', 'read', 'group'];

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }
}
