<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisplayStatusToArticleCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_category', function (Blueprint $table) {
            $table->boolean('display_status')->default(1)->comment('1 展示 2 隐藏 ');
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
            $table->dropColumn('display_status');
        });
    }
}
