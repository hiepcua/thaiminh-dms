<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyOrderHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_order_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('agency_order_id');
            $table->text('current_info');
            $table->text('current_items');
            $table->text('old_info');
            $table->text('old_items');
            $table->unsignedBigInteger('updated_by');
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
        Schema::dropIfExists('agency_order_history');
    }
}
