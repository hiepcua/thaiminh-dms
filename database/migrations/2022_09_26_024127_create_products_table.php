<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code', 50);
            $table->integer('parent_id')->nullable()->default(0);
            $table->string('desc')->nullable();
            $table->tinyInteger('company_id')->comment('1=TM, 2=WC, 3=HC');
            $table->decimal('wholesale_price', 20, 0)->comment('gia ban buon');
            $table->decimal('price', 20, 0)->comment('gia khuyen nghi');
            $table->string('status')->comment('1 = active, 0 = inactive');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->integer('key_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
