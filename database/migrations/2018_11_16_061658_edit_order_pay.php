<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOrderPay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_pay', function (Blueprint $table) {
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_pay', function (Blueprint $table) {
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除')->change();
        });
    }
}
