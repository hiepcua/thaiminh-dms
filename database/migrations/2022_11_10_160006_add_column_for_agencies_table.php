<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnForAgenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('pay_number')->nullable();
            $table->decimal('pay_service_cost')->nullable();
            $table->tinyInteger('pay_personal_tax')->nullable()->comment('1: co thue TNCN, 2: ko co thue TNCN');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('pay_number')->nullable();
            $table->dropColumn('pay_service_cost')->nullable();
            $table->dropColumn('pay_personal_tax')->nullable()->comment('1: co thue TNCN, 2: ko co thue TNCN');
        });
    }
}
