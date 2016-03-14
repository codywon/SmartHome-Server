<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Trigger extends Model
{
    protected $table = 'triggers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['condition_device', 'condition_action', 'trigger_device', 'trigger_action', 'group'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }

}
