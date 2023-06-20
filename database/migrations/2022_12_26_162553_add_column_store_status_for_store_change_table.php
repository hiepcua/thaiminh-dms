<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnStoreStatusForStoreChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_changes', function (Blueprint $table) {
            $table->tinyInteger('store_status')->nullable()->comment('status of store');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_changes', function (Blueprint $table) {
            $table->dropColumn('store_status');
        });
    }
}
