<?php

namespace smarthome;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'phone', 'password', 'group', 'address', 'sex'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'group_password', 'remember_token'];

    public function devices()
    {
         return $this->hasMany('smarthome\Device');
    }

    public function messages()
    {
        return $this->hasMany('smarthome\Message');
    }

    public function rooms()
    {
        return $this->hasMany('smarthome\Room');
    }

    public function scenes()
    {
        return $this->hasMany('smarthome\Scene');
    }
}
