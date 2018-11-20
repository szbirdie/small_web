<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditSpecs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('specs', function (Blueprint $table) {

            $table->dropColumn('unit');
            $table->dropColumn('data');
            $table->renameColumn('cate_id', 'category_id');
            $table->integer('order')->comment('倒叙')->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('specs', function (Blueprint $table) {

            $table->string('unit', 255)->comment('规格单位')->default('');
            $table->string('data', 255)->comment('规格数据')->default('');
            $table->renameColumn('category_id', 'cate_id');
            $table->dropColumn('order');


        });
    }
}
