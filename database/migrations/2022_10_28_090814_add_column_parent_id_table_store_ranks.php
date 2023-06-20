<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnParentIdTableStoreRanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('store_ranks')) {
            Schema::table('store_ranks', function (Blueprint $table) {
                $table->integer('store_parent_id')->nullable();
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
        if (Schema::hasTable('store_ranks')) {
            Schema::table('store_ranks', function (Blueprint $table) {
                $table->dropColumn('store_parent_id');
            });
        }
    }
}
