<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnProductTypeToProductGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_groups', function (Blueprint $table) {
            //
            $table->tinyInteger('product_type')
                ->comment('Sử dụng làm phân loại sản phẩm. Giá trị  1 = Hàng quảng cáo, 2 = Hàng tư vấn , 3 = Độc quyền')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_groups', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
}
