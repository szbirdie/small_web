<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditIndexData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('index_data', function (Blueprint $table) {
            $table->renameColumn('steelDate', 'steel_at');
            $table->renameColumn('indiceType', 'indice_type');
            $table->renameColumn('steelValue', 'steel_value');
            $table->renameColumn('steelZde', 'steel_zde');
            $table->renameColumn('steelZdf', 'steel_zdf');
            $table->renameColumn('indiceArea', 'indice_area');
            $table->renameColumn('steelRemark', 'steel_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('index_data', function (Blueprint $table) {
            $table->renameColumn('steel_at', 'steelDate');
            $table->renameColumn('indice_type', 'indiceType');
            $table->renameColumn('steel_value', 'steelValue');
            $table->renameColumn('steel_zde', 'steelZde');
            $table->renameColumn('steel_zdf', 'steelZdf');
            $table->renameColumn('indice_area', 'indiceArea');
            $table->renameColumn('steel_description', 'steelRemark');
        });
    }
}
