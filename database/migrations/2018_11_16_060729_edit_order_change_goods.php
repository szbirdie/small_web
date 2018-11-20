<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOrderChangeGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_change_goods', function (Blueprint $table) {
            $table->renameColumn('price', 'order_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_change_goods', function (Blueprint $table) {
            $table->renameColumn('order_price', 'price');
        });
    }
}
