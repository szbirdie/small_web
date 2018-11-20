<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditLogOrderOperation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_order_operation', function (Blueprint $table) {
            $table->renameColumn('op_mark', 'op_description');
            $table->renameColumn('goods_id_path', 'goods_change_path');
            $table->renameColumn('total_price_path', 'total_price_change_path');
            $table->renameColumn('price_path', 'price_change_path');
            $table->renameColumn('weight_path', 'weight_change_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_order_operation', function (Blueprint $table) {
            $table->renameColumn('op_description', 'op_mark');
            $table->renameColumn('goods_change_path', 'goods_id_path');
            $table->renameColumn('total_price_change_path', 'total_price_path');
            $table->renameColumn('price_change_path', 'price_path');
            $table->renameColumn('weight_change_path', 'weight_path');
        });
    }
}
