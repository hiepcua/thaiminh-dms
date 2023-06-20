<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnOrderCodePrefixToOrderCodeOfAgencyOrderFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_order_files', function (Blueprint $table) {
            $table->renameColumn('order_code_prefix', 'order_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agency_order_files', function (Blueprint $table) {
            $table->renameColumn('order_code', 'order_code_prefix');
        });
    }
}
