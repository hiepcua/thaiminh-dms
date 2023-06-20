<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevenueProductConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revenue_product_conditions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('revenue_period_item_id');
            $table->integer('product_id');
            $table->tinyInteger('min_box');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('revenue_product_conditions');
    }
}
