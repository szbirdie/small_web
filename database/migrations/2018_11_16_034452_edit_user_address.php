<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditUserAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_address', function (Blueprint $table) {
            //
            $table->string('consignee_phone', 64)->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->renameColumn('is_default', 'default_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_address', function (Blueprint $table) {

            $table->string('consignee_phone', 64)->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->renameColumn('default_status', 'is_default');

            //
        });
    }
}
