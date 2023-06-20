<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountDetailToStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->longText('discount_detail')->nullable();
        });
        Schema::table('store_order_items', function (Blueprint $table) {
            $table->longText('discount_detail')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->dropColumn('discount_detail');
        });
        Schema::table('store_order_items', function (Blueprint $table) {
            $table->dropColumn('discount_detail');
        });
    }
}
