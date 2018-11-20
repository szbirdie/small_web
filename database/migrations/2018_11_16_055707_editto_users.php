<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EdittoUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('last_login_ip');
            $table->dropColumn('login_num');
            $table->dropColumn('last_login_time');
            $table->renameColumn('company_is_admin', 'company_admin_status');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();

            $table->tinyInteger('authorization_status')->default(1)->comment('授权状态 1 未授权 2 已授权');
            $table->renameColumn('user_name', 'real_name');
            $table->dropColumn('password');
            $table->renameColumn('name', 'nick_name');

            $table->renameColumn('sign_accountId', 'sign_account_id');
            $table->renameColumn('sign_organize_accountId', 'sign_organize_account_id');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('last_login_ip', 255)->default('上次登录ip');
            $table->integer('login_num')->comment('登录次数')->default(0);
            $table->integer('last_login_time')->comment('上次登录时间')->default(0);
            $table->renameColumn('company_admin_status', 'company_is_admin');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();

            $table->dropColumn('authorization_status')->comment('状态 :1正常 2删除 默认1')->change();
            $table->renameColumn('real_name', 'user_name');
            $table->string('password', 64)->default('');
            $table->renameColumn('nick_name', 'name');

            $table->renameColumn('sign_account_id', 'sign_accountId');
            $table->renameColumn('sign_organize_account_id', 'sign_organize_accountId');


            //
        });
    }
}
