<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTableAgencyOrderItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_order_items', function (Blueprint $table) {
            $table->integer('product_group_id')->nullable()->change();
            $table->integer('product_sub_group_id')->nullable()->change();
            $table->integer('product_priority')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_order_items', function (Blueprint $table) {
            $table->integer('product_group_id')->change();
            $table->integer('product_sub_group_id')->change();
            $table->integer('product_priority')->change();
        });
    }
}
