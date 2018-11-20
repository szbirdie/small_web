<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditTourists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tourists', function (Blueprint $table) {
            //
            $table->dropColumn('login_num');
            $table->renameColumn('current_login_time', 'current_login_at');
            $table->renameColumn('last_login_time', 'last_login_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tourists', function (Blueprint $table) {
            //
            $table->integer('login_num')->comment('登录次数')->default(0);
            $table->renameColumn('current_login_at', 'current_login_time');
            $table->renameColumn('last_login_at', 'last_login_time');
        });
    }
}
