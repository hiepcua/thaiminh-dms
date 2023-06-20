<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('store_order_id')->index('store_order_id');
            $table->string('product_type', 10)->comment('product, gift, discount');
            $table->integer('product_id');
            $table->integer('product_group_id')->nullable();
            $table->integer('product_sub_group_id')->nullable();
            $table->integer('product_priority')->nullable();
            $table->string('product_name');
            $table->decimal('product_price', 20, 0)->default(0);
            $table->smallInteger('product_qty')->default(0);
            $table->decimal('discount', 20, 0)->default(0);
            $table->decimal('sub_total', 20, 0)->default(0);
            $table->decimal('total_amount', 20, 0)->default(0);
            $table->string('note')->nullable();
            $table->string('booking_at', 10)->nullable()->index('booking_at');
            $table->bigInteger('promo_id')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_order_items');
    }
}
