<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AfterColumnPeriodToProductTypeToRevenuePeriods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revenue_periods', function (Blueprint $table) {
            $table->date('period_to', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revenue_periods', function (Blueprint $table) {
            $table->date('period_to', 50)->nullable(true)->change();
        });
    }
}
