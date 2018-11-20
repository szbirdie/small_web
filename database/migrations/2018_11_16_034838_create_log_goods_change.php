<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogGoodsChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_goods_change', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('goods_id')->nullable()->default(0)->comment('商品ID');
            $table->string('price_path', 50)->default('0')->comment('价格变动');
            $table->string('sale_price_path', 50)->default('0')->comment('售价变动');
            $table->string('weight_path', 50)->default('0')->comment('重量变动');
            $table->string('sale_weight_path', 50)->default('0')->comment('分配重量变动');
            $table->string('lock_weight_path', 50)->default('0')->comment(' 锁定重量变动');
            $table->string('type', 10)->default('0')->comment('方式');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('log_goods_change');
    }
}
