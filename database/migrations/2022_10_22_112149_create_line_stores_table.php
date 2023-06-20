<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_stores', function (Blueprint $table) {
            $table->id();
            $table->integer('line_id')->nullable(false);
            $table->integer('store_id')->nullable(false);
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->integer('number_visit')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->tinyInteger('status')->default(1)->comment("active: 1, inactive: 2, pending: 3");
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
        Schema::dropIfExists('line_stores');
    }
}
