<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditCommentUsers extends Migration
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

            $table->string('real_name', 50)->comment('真实姓名')->change();
            $table->string('nick_name', 50)->comment('昵称')->change();
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
            $table->string('real_name', 50)->comment('真实姓名')->change();
            $table->string('nick_name', 50)->comment('昵称')->change();
            //
        });
    }
}
