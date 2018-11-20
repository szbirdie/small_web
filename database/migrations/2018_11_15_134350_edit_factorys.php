<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditFactorys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factorys', function (Blueprint $table) {
            $table->renameColumn('relator', 'relator_name');
            $table->renameColumn('phone', 'relator_phone');
            $table->renameColumn('thumb', 'logo_img');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factorys', function (Blueprint $table) {
            $table->renameColumn('relator_name', 'relator');
            $table->renameColumn('relator_phone', 'phone');
            $table->renameColumn('logo_img', 'thumb');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
        });
    }
}
