<?php

use Illuminate\Support\Facades\Schema;
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
        Schema::create('res_users', function (Blueprint $table) {
            $table->increments('id');
            //$table->string('name');
            //$table->string('surname');
            //$table->string('role');
            //$table->string('email')->unique();
            $table->string('login')->unique();
            //$table->string('password');
            $table->string('password_crypt');
            //$table->rememberToken();
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('res_users');
    }
}