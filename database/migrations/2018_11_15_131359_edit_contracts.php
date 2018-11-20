<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditContracts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('model_id');
            $table->dropColumn('code');
            $table->renameColumn('contract_url', 'contract_img');
            $table->renameColumn('uesr_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->renameColumn('contract_img', 'contract_url');
            $table->renameColumn('user_id', 'uesr_id');
        });
    }
}
