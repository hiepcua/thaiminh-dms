<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportRevenueStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('report_revenue_store')) {
            Schema::create('report_revenue_store', function (Blueprint $table) {
                $table->id()->autoIncrement();
                $table->date('day')->nullable();
                $table->integer('store_id')->nullable();
                $table->integer('total_order')->nullable();
                $table->integer('total_product')->nullable();
                $table->double('total_sub_amount')->nullable();
                $table->double('total_discount')->nullable();
                $table->double('total_amount')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_revenue_store');
    }
}
