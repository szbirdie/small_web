<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditLogClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_client', function (Blueprint $table) {
            $table->renameColumn('lng', 'longitude');
            $table->renameColumn('lat', 'latitude');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_client', function (Blueprint $table) {
            $table->renameColumn('longitude', 'lng');
            $table->renameColumn('latitude', 'lat');

        });
    }
}
