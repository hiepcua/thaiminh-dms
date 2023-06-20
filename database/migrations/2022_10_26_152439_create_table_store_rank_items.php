<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStoreRankItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_rank_items', function (Blueprint $table) {
            $table->id();
            $table->string('store_rank_unique_key', 12);
            $table->string('unique_key', 12);
            $table->unsignedBigInteger('store_id');
            $table->string('from_date', 10);
            $table->string('to_date', 10);
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('sub_group_id');
            $table->tinyInteger('product_type');
            $table->integer('product_id');
            $table->integer('product_id_key');
            $table->integer('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_rank_items');
    }
}
