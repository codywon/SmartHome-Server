<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

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
        factory('smarthome\User', 50)->create()->each(function($u) {
            $count = 10;
            while($count--)
            {
                $u->devices()->save(factory('smarthome\Device')->make());
                $u->rooms()->save(factory('smarthome\Room')->make());
                $u->messages()->save(factory('smarthome\Message')->make());
                $u->scenes()->save(factory('smarthome\Scene')->make());
            }
        });
    }
}
