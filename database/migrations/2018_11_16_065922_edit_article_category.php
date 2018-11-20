<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditArticleCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_category', function (Blueprint $table) {
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
        Schema::table('article_category', function (Blueprint $table) {
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
        });
    }
}
