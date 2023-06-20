<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyOrderFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_order_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_order_id');
            $table->unsignedBigInteger('agency_id');
            $table->integer('qty_store_order_merged');
            $table->string('order_code_prefix', 255);
            $table->integer('item_qty');
            $table->double('cost');
            $table->double('discount');
            $table->double('final_cost');
            $table->string('file_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agency_order_files');
    }
}
