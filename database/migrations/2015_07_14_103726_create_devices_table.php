<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('room_id')->unsigned();
            $table->string('name');
            $table->integer('type')->unsigned();
            $table->integer('brand')->nullable();
            $table->integer('model')->nullable();
            $table->string('imei')->unique();
            $table->string('nodeID');
            $table->string('address')->nullable();
            $table->boolean('infrared')->default(false);
            $table->integer('status')->default(0);
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('devices');
    }
}
