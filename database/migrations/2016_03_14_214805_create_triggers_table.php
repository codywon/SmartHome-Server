<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triggers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('condition_device')->unsigned();
            $table->integer('condition_action')->unsigned();
            $table->integer('trigger_device')->unsigned();
            $table->integer('trigger_action')->unsigned();
            $table->string('group')->nullable;
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('triggers');
    }
}
