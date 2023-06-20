<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTableReportRevenueOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_revenue_order', function (Blueprint $table) {
            $table->integer('user_id')->comment(' Trình dược viên')->change();
            $table->integer('total_quantity')->comment('Tổng số lượng sản phẩm')->change();
            $table->integer('sub_group_id')->nullable();
            $table->integer('asm_user_id')->nullable()->comment('ASM');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_revenue_order', function (Blueprint $table) {
            $table->dropColumn('sub_group_id');
            $table->dropColumn('asm_user_id');
        });
    }
}
