<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->integer('organization_id')->nullable();
            $table->bigInteger('agency_id')->nullable();
            $table->string('code', 20)->nullable();
            $table->string('booking_at', 10)->index('booking_at');
            $table->string('delivery_at', 10)->nullable();
            $table->decimal('discount', 20, 0)->nullable();
            $table->decimal('sub_total', 20, 0)->nullable()->default(0);
            $table->decimal('total_amount', 20, 0)->nullable()->default(0);
            $table->bigInteger('store_id');
            $table->string('store_code', 20)->nullable();
            $table->integer('store_organization_id')->nullable();
            $table->integer('store_province_id')->nullable();
            $table->integer('store_district_id')->nullable();
            $table->integer('store_ward_id')->nullable();
            $table->boolean('paid')->nullable()->default(false);
            $table->text('note')->nullable();
            $table->tinyInteger('status')->comment('1=chua giao, 2=da giao, 3=da huy, 4=tra lai, 5=da dat, 6=xoa don');
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
        Schema::dropIfExists('store_orders');
    }
}
