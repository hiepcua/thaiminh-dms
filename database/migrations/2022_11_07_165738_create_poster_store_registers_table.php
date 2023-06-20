<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosterStoreRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poster_store_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('store_id');
            $table->integer('poster_id');
            $table->integer('tdv_id');
            $table->integer('status')->default(1);
            $table->float('poster_height');
            $table->float('poster_width');
            $table->float('poster_area');
            $table->string('poster_note', 255)->nullable();
            $table->string('note', 255)->nullable();
            $table->string('title', 255)->nullable();

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
        Schema::dropIfExists('poster_store_registers');
    }
}
