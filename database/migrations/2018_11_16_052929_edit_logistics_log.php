<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditLogisticsLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('logistics_log', 'logistics_history');
        Schema::table('logistics_history', function (Blueprint $table) {
            $table->string('car_no', 255)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('logistics_history', 'logistics_log');
        Schema::table('logistics_log', function (Blueprint $table) {
            $table->string('car_no', 16)->default('')->change();
        });
    }
}
