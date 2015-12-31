<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use smarthome\Device;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Model::unguard();
        //$this->call(UserTableSeeder::class);
        //Model::reguard();

        //$userNum = 50;
        //$count = 10;
        //factory('smarthome\User', $userNum)->create()->each(function($u) {
            //$deviceNum = 10;
            //while($deviceNum--)
            //{
                //$u->devices()->save(factory('smarthome\Device')->make());
                //$u->rooms()->save(factory('smarthome\Room')->make());
                //$u->messages()->save(factory('smarthome\Message')->make());
                //$u->scenes()->save(factory('smarthome\Scene')->make());
            //}
        //});

        //$propertyNum = $count*$userNum;
        //while($propertyNum)
        //{
            //Device::find($propertyNum)->properties()->save(factory('smarthome\DeviceProperty')->make());
            //Device::find($propertyNum)->properties()->save(factory('smarthome\DeviceProperty')->make());
            //Device::find($propertyNum)->properties()->save(factory('smarthome\DeviceProperty')->make());
            //Device::find($propertyNum)->properties()->save(factory('smarthome\DeviceProperty')->make());
            //Device::find($propertyNum)->properties()->save(factory('smarthome\DeviceProperty')->make());
            //$propertyNum--;
        //}
    }
}
