<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_stores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('type')->default(1)->comment('1 = le | 2 = chuoi | 3 = cho');
            $table->string('name');
            $table->string('code', 20)->nullable()->index();
            $table->integer('organization_id');
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('ward_id')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_owner', 50);
            $table->string('phone_web', 50)->nullable();
            $table->double('lng')->nullable();
            $table->double('lat')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->boolean('vat_parent')->nullable()->default(false);
            $table->string('vat_buyer')->nullable();
            $table->string('vat_company')->nullable();
            $table->string('vat_address')->nullable();
            $table->string('vat_number', 20)->nullable();
            $table->string('vat_email', 100)->nullable();
            $table->string('line_day')->nullable()->comment('1 = thu 2,2,3,4,5,6');
            $table->string('line_period')->nullable()->comment('1 = 1 tuan, 2, 3, 4');
            $table->text('note_private')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->boolean('show_web')->nullable()->default(true);
            $table->string('file_id')->nullable();
            $table->integer('is_disabled')->nullable()->default(0);
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
        Schema::dropIfExists('new_stores');
    }
}
