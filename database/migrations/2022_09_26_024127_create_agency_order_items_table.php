<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('agency_order_id');
            $table->string('product_type', 10)->comment('product, gift');
            $table->integer('product_id');
            $table->integer('product_group_id');
            $table->integer('product_sub_group_id');
            $table->integer('product_priority');
            $table->string('product_name');
            $table->decimal('product_price', 20, 0)->default(0);
            $table->smallInteger('product_qty')->default(0);
            $table->decimal('discount', 20, 0)->default(0);
            $table->decimal('sub_total', 20, 0)->default(0);
            $table->decimal('total_amount', 20, 0)->default(0);
            $table->string('note')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_order_items');
    }
}
