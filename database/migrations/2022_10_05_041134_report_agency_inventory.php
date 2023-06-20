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
        if (!Schema::hasTable('report_agency_inventory')) {
            Schema::create('report_agency_inventory', function (Blueprint $table) {
                $table->id()->autoIncrement();
                $table->dateTime('timestamp_x')->nullable();
                $table->dateTime('update')->nullable();
                $table->integer('year')->nullable();
                $table->integer('month')->nullable();
                $table->integer('agency_id')->nullable();
                $table->integer('product_id')->nullable();
                $table->integer('start_num')->nullable();
                $table->integer('import_num')->nullable();
                $table->integer('export_num')->nullable();
                $table->integer('inventory_num')->nullable();
                $table->integer('inventory_real_num')->nullable();
                $table->dateTime('inventory_real_input_date')->nullable();
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
        Schema::dropIfExists('report_agency_inventory');
    }
};
