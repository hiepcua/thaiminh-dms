<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('username', 50)->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('phone', 50)->nullable();
            $table->date('dob')->nullable();
            $table->string('position')->nullable();
            $table->bigInteger('agency_id')->nullable();
            $table->tinyInteger('status')->comment('1 = active, 0 = inactive');
            $table->string('last_login_ip', 20)->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->boolean('change_pass')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->bigInteger('parent_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
