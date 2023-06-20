<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTtkStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_orders', function (Blueprint $table) {
            $table->tinyInteger('order_type')->nullable()->default(1)->comment('1 = don thuong, 2 = don tt key');
            $table->bigInteger('order_logistic')->nullable()->default(0)->comment('1=viettel, tdv=user_id');
            $table->string('order_logistic_type', 20)->nullable()->comment('viettel, tdv');
            $table->bigInteger('parent_id')->nullable()->default(0)->comment('0=don thuong hoac don TTK goc, don TTK con = ID don TTK goc.');
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
            $table->dropColumn('order_type');
            $table->dropColumn('order_logistic');
            $table->dropColumn('order_logistic_type');
            $table->dropColumn('parent_id');
        });
    }
}
