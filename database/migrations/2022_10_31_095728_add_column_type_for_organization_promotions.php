<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTypeForOrganizationPromotions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_promotions', function (Blueprint $table) {
            $table->tinyInteger('type')->nullable()->comment('1: include, 2: exclude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_promotions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
