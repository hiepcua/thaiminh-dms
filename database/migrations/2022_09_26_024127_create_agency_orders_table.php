<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgencyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->dateTime('booking_at');
            $table->decimal('total_amount', 20, 0);
            $table->bigInteger('agency_id');
            $table->integer('agency_province_id')->nullable();
            $table->string('agency_address')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('type')->comment('1 = DL nhap hang, 2 = TDV len don');
            $table->tinyInteger('status')->comment('1 = Chua TT, 2 = Da TT, 4 = Huy don');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('agency_orders');
    }
}
