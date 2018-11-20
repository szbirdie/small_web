<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditIndexArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('index_area', function (Blueprint $table) {
            $table->renameColumn('indiceAreaid', 'indice_area_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('index_area', function (Blueprint $table) {
            $table->renameColumn('indice_area_id', 'indiceAreaid');
        });
    }
}
