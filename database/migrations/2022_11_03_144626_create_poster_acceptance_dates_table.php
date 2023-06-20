<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosterAcceptanceDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poster_acceptance_dates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('poster_id');
            $table->timestamp('acceptance_start_date')->nullable();
            $table->timestamp('acceptance_end_date')->nullable();
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
        Schema::dropIfExists('poster_acceptance_dates');
    }
}
