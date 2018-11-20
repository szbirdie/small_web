<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_goods', 'order_goods_json');
            $table->renameColumn('order_marks', 'description');
            $table->renameColumn('is_pay', 'pay_status');
            $table->renameColumn('last_update_user', 'last_update_name');
            $table->dropColumn('state');
            $table->renameColumn('contracts_status', 'contract_status');
            $table->renameColumn('order_confirm', 'confirm_status');
            $table->renameColumn('order_payment', 'payment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_goods_json', 'order_goods');
            $table->renameColumn('description', 'order_marks');
            $table->renameColumn('pay_status', 'is_pay');
            $table->renameColumn('last_update_name', 'last_update_user');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除');
            $table->renameColumn('contract_status', 'contracts_status');
            $table->renameColumn('confirm_status', 'order_confirm');
            $table->renameColumn('payment_status', 'order_payment');
        });
    }
}
