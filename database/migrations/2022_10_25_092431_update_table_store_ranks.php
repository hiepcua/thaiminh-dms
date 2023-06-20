<?php

use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableStoreRanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Type::hasType('double')) {
            Type::addType('double', FloatType::class);
        }

        if (Schema::hasTable('store_ranks')) {
            Schema::table('store_ranks', function (Blueprint $table) {
                $table->string('rank')->nullable()->change();
                $table->integer('rank_id')->nullable()->change();

                $table->double('revenue',12,0)->default(0)->change();
                $table->double('bonus',12,0)->default(0)->change();

                $table->tinyInteger('product_type')->nullable();
                $table->tinyInteger('period')->nullable();

                $table->float('rate_product_priority')->nullable();
                $table->double('bonus_product_priority')->nullable();

                $table->tinyInteger('is_product_priority')->default(0)->nullable();

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('store_ranks')) {
            Schema::table('store_ranks', function (Blueprint $table) {
                $table->dropColumn('product_tpye');
                $table->dropColumn('period');

                $table->dropColumn('rate_product_priority');
                $table->dropColumn('bonus_product_priority');
                $table->dropColumn('is_product_priority');
            });
        }
    }
}
