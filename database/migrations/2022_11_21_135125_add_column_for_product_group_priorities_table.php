<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnForProductGroupPrioritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_group_priorities', function (Blueprint $table) {
            $table->integer('store_type')->nullable()->comment("1: le, 2: chuoi, 3: cho");
            $table->integer('region_apply')->nullable()->comment("1: mien bac, 2: mien nam, 3: mien trung, 6: ca nuoc");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_group_priorities', function (Blueprint $table) {
            $table->dropColumn('store_type');
            $table->dropColumn('region_apply');
        });
    }
}
