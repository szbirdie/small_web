<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditRecommend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recommend', function (Blueprint $table) {
            $table->renameColumn('position_id', 'recommend_position_id');
            $table->renameColumn('thumb', 'thumb_img');
            $table->integer('creator_id')->default(0)->comment('创建人ID')->change();
            $table->renameColumn('params', 'param_json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recommend', function (Blueprint $table) {
            $table->renameColumn('recommend_position_id', 'position_id');
            $table->renameColumn('thumb_img', 'thumb');
            $table->integer('creator_id')->default(0)->comment('创建人ID')->change();
            $table->renameColumn('param_json', 'params');
        });
    }
}
