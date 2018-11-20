<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditLogistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistics', function (Blueprint $table) {
            $table->renameColumn('phone', 'car_phone');
            $table->renameColumn('name', 'car_name');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->dropColumn('first_car_no');
            $table->dropColumn('first_phone');
            $table->dropColumn('first_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logistics', function (Blueprint $table) {
            $table->renameColumn('car_phone', 'phone');
            $table->renameColumn('car_name', 'name');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->string('first_car_no', 20)->default('')->comment('队长车牌号');
            $table->string('first_phone', 20)->default('')->comment('队长电话');
            $table->string('first_name', 20)->default('')->comment('队长姓名');
        });
    }
}
