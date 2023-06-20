<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agency_order_items', function (Blueprint $table) {
            $table->integer('product_qty')->default(0)->change();
        });
        Schema::table('product_group_priorities', function (Blueprint $table) {
            $table->boolean('priority')->default(0)->nullable()->change();
        });
        Schema::table('provinces', function (Blueprint $table) {
            if (!Schema::hasColumn('provinces', 'region')) {
                $table->string('region', 20)->nullable();
            }
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->change();
            if (!Schema::hasColumn('stores', 'code_2')) {
                $table->string('code_2', 50)->nullable()->after('code');
            } else {
                $table->string('code_2', 50)->nullable()->after('code')->change();
            }
            $table->integer('organization_id')->nullable()->change();
            $table->string('phone_owner', 50)->nullable()->change();
            if (!Schema::hasColumn('stores', 'start_booking')) {
                $table->string('start_booking', 10)->nullable()->after('status');
                $table->string('last_booking', 10)->nullable()->after('start_booking');
                $table->boolean('organization_predict')->default(0);
            }
            if (!Schema::hasColumn('stores', 'file_id')) {
                $table->string('file_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
