<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIncludeProductsAndExcludeProductsAndNamePromotionCondition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotion_conditions', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->dropColumn('product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotion_conditions', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->text('product')->default("[]");
        });
    }
}
