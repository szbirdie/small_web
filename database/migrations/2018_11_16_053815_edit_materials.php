<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->renameColumn('cate_id', 'category_id');
            $table->integer('creator_id')->default(0)->comment('创建人ID')->change();
            $table->integer('order')->default(0)->comment('倒叙');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->renameColumn('category_id', 'cate_id');
            $table->integer('creator_id')->default(0)->comment('创建人ID')->change();
            $table->dropColumn('order');
        });
    }
}
