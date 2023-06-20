<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevenuePeriodItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revenue_period_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->bigInteger('revenue_period_id');
            $table->integer('rank_id');
            $table->integer('group_id');
            $table->integer('sub_group_id');
            $table->decimal('revenue', 20, 0);
            $table->double('discount_rate');
            $table->double('priority_discount_rate');
            $table->tinyInteger('priority_product_min');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('revenue_period_items');
    }
}
