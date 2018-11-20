<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditGoods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->renameColumn('materials_id', 'material_id');
            $table->renameColumn('materials_name', 'material_name');
            $table->renameColumn('factorys_id', 'factory_id');
            $table->renameColumn('factorys_name', 'factory_name');
            $table->renameColumn('storehouses_id', 'storehouse_id');
            $table->renameColumn('storehouses_name', 'storehouse_name');
            $table->renameColumn('big_categorys_id', 'category_big_id');
            $table->renameColumn('big_categorys_name', 'category_big_name');
            $table->renameColumn('recommend', 'recommend_status');
            $table->dropColumn('distrib_level');
            $table->renameColumn('small_categorys_id', 'category_small_id');
            $table->renameColumn('small_categorys_name', 'category_small_name');
            $table->boolean('display_status')->default(1)->comment('1 展示 2 下架');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->renameColumn('material_id', 'materials_id');
            $table->renameColumn('material_name', 'materials_name');
            $table->renameColumn('factory_id', 'factorys_id');
            $table->renameColumn('factory_name', 'factorys_name');
            $table->renameColumn('storehouse_id', 'storehouses_id');
            $table->renameColumn('storehouse_name', 'storehouses_name');
            $table->renameColumn('category_big_id', 'big_categorys_id');
            $table->renameColumn('category_big_name', 'big_categorys_name');
            $table->renameColumn('recommend_status', 'recommend');
            $table->string('distrib_level')->default('')->comment('被分配过的登记，json数组');
            $table->renameColumn('category_small_id', 'small_categorys_id');
            $table->renameColumn('category_small_name', 'small_categorys_name');
            $table->dropColumn('display_status');
        });
    }
}
