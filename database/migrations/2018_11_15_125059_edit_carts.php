<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('categorys_id', 'category_id');
            $table->renameColumn('categorys_name', 'category_name');
            $table->renameColumn('materials_id', 'material_id');
            $table->renameColumn('materials_name', 'material_name');
            $table->renameColumn('storehouses_id', 'storehouse_id');
            $table->renameColumn('storehouses_name', 'storehouse_name');
            $table->renameColumn('factorys_id', 'factory_id');
            $table->renameColumn('factorys_name', 'factory_name');
            $table->renameColumn('checked', 'checked_status');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->renameColumn('small_categorys_id', 'category_small_id');
            $table->renameColumn('small_categorys_name', 'category_small_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('category_id', 'categorys_id');
            $table->renameColumn('category_name', 'categorys_name');
            $table->renameColumn('material_id', 'materials_id');
            $table->renameColumn('material_name', 'materials_name');
            $table->renameColumn('storehouse_id', 'storehouses_id');
            $table->renameColumn('storehouse_name', 'storehouses_name');
            $table->renameColumn('factory_id', 'factorys_id');
            $table->renameColumn('factory_name', 'factorys_name');
            $table->renameColumn('checked_status', 'checked');
            $table->integer('state')->unsigned()->default(1)->comment('状态 :1正常 2删除 默认1')->change();
            $table->renameColumn('category_small_id', 'small_categorys_id');
            $table->renameColumn('category_small_name', 'small_categorys_name');
        });
    }
}
