<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Storehouses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storehouses', function (Blueprint $table) {
            //
            $table->renameColumn('relator', 'relator_name');
            $table->dropColumn('logo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storehouses', function (Blueprint $table) {
            $table->renameColumn('relator_name', 'relator');
            $table->string('logo', 255)->default('');
        });
    }
}
