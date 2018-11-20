<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditLogExcelOperation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_excel_operation', function (Blueprint $table) {
            $table->renameColumn('excel_url', 'excel_path');
            $table->string('purpose', 10)->default('0')->comment('操作');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_excel_operation', function (Blueprint $table) {
            $table->renameColumn('excel_path', 'excel_url');
            $table->dropColumn('purpose');
        });
    }
}
