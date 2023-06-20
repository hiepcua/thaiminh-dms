<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixLineStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_stores', function (Blueprint $table) {
            if(!Schema::hasColumn('line_stores', 'id')){
                $table->id();
            }
            if(!Schema::hasColumn('line_stores', 'line_id')){
                $table->integer('line_id')->nullable(false);
            }
            if(!Schema::hasColumn('line_stores', 'store_id')){
                $table->integer('store_id')->nullable(false);
            }
            if(!Schema::hasColumn('line_stores', 'from')){
                $table->date('from')->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'to')){
                $table->date('to')->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'number_visit')){
                $table->integer('number_visit')->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'reference_type')){
                $table->string('reference_type', 50)->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'created_by')){
                $table->bigInteger('created_by')->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'updated_by')){
                $table->bigInteger('updated_by')->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'status')){
                $table->tinyInteger('status')->default(1)->comment("active: 1, inactive: 2, pending: 3, close: 4");
            }
            if(!Schema::hasColumn('line_stores', 'created_at')){
                $table->timestamp('created_at')->nullable();
            }
            if(!Schema::hasColumn('line_stores', 'updated_at')){
                $table->timestamp('updated_at')->nullable();
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
        Schema::table('line_stores', function (Blueprint $table) {
            if(Schema::hasColumn('line_stores', 'id')){
                $table->dropColumn('id');
            }
            if(Schema::hasColumn('line_stores', 'line_id')){
                $table->dropColumn('line_id');
            }
            if(Schema::hasColumn('line_stores', 'store_id')){
                $table->dropColumn('store_id');
            }
            if(Schema::hasColumn('line_stores', 'from')){
                $table->dropColumn('from');
            }
            if(Schema::hasColumn('line_stores', 'to')){
                $table->dropColumn('to');
            }
            if(Schema::hasColumn('line_stores', 'number_visit')){
                $table->dropColumn('number_visit');
            }
            if(Schema::hasColumn('line_stores', 'reference_type')){
                $table->dropColumn('reference_type', 50);
            }
            if(Schema::hasColumn('line_stores', 'created_by')){
                $table->dropColumn('created_by');
            }
            if(Schema::hasColumn('line_stores', 'updated_by')){
                $table->dropColumn('updated_by');
            }
            if(Schema::hasColumn('line_stores', 'status')){
                $table->dropColumn('status');
            }
            if(Schema::hasColumn('line_stores', 'created_at')){
                $table->dropColumn('created_at');
            }
            if(Schema::hasColumn('line_stores', 'updated_at')){
                $table->dropColumn('updated_at');
            }
        });
    }
}
