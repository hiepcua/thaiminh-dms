<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldToPosterStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('poster_store_registers', function (Blueprint $table) {

            $table->integer('type')->default(0);
            $table->dateTime('offer_date')->nullable();
            $table->string('offer_reason', 255)->nullable();
            $table->string('offer_reply', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('poster_store_registers', function (Blueprint $table) {
            //
        });
    }
}
