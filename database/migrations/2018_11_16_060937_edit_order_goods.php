<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOrderGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_goods', function (Blueprint $table) {
            $table->renameColumn('weight', 'goods_weight');
            $table->renameColumn('price', 'unit_price');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除')->change();
            $table->renameColumn('goods_info', 'goods_info_json');
            $table->renameColumn('is_send', 'send_status');
            $table->renameColumn('admin_mark', 'seller_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_goods', function (Blueprint $table) {
            $table->renameColumn('goods_weight', 'weight');
            $table->renameColumn('unit_price', 'price');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除')->change();
            $table->renameColumn('goods_info_json', 'goods_info');
            $table->renameColumn('send_status', 'is_send');
            $table->renameColumn('seller_description', 'admin_mark');
        });
    }
}
