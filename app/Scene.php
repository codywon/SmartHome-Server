<?php

namespace smarthome;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $table = 'scenes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'is_default', 'default_icon', 'devices', 'group'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['icon'];

    public function user()
    {
        return $this->belongsTo('smarthome\User');
    }
}
