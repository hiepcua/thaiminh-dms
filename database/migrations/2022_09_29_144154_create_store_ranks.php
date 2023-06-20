<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreRanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('unique_key', 12);
            $table->unsignedBigInteger('store_id');
            $table->string('from_date', 10);
            $table->string('to_date', 10);
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('sub_group_id');
            $table->unsignedBigInteger('rank_id');
            $table->string('rank', 50);
            $table->integer('revenue');
            $table->integer('priority');
            $table->float('rate');
            $table->integer('bonus');
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
        Schema::dropIfExists('store_ranks');
    }
}
