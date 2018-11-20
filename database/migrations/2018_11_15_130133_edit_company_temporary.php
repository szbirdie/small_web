<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditCompanyTemporary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_temporary', function (Blueprint $table) {
            $table->renameColumn('relator', 'relator_name');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
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
        Schema::table('company_temporary', function (Blueprint $table) {
            $table->renameColumn('relator_name', 'relator');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->renameColumn('sign_organize_account_id', 'sign_organize_accountId');
        });
    }
}
