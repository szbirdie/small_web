<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameArticleCate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('article_cate', function (Blueprint $table) {
            Schema::rename('article_cate', 'article_category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('article_cate', function (Blueprint $table) {
            Schema::rename('article_category', 'article_cate');
        });
    }
}
