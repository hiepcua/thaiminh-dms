<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('report_revenue_order')) {
            Schema::create('report_revenue_order', function (Blueprint $table) {
                $table->id()->autoIncrement();
                $table->date('day')->nullable();
                $table->integer('period')->nullable();
                $table->integer('user_id')->nullable();
                $table->integer('store_id')->nullable();
                $table->integer('product_id')->nullable();
                $table->integer('product_group_id')->nullable();
                $table->integer('organization_id')->nullable();
                $table->integer('total_quantity')->nullable();
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
        Schema::dropIfExists('report_revenue_order');
    }
};
