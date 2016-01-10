<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable;
            $table->string('email')->nullable;
            $table->string('phone')->unique();
            $table->string('password', 60);
            $table->integer('role')->default(0);
            $table->string('group')->nullable;
            $table->string('group_password', 60)->nullable;
            $table->boolean('sex')->default(true);
            $table->string('address')->nullable;
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
