<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMsgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_msg', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable();
            $table->foreignId('organization_id')->nullable();
            
            $table->foreign('period_id')->references('id')->on('tbl_period')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('organization_id')->references('id')->on('tbl_organization')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign(['period_id','organization_id']);
        $table->dropColumn(['period_id','organization_id']);
    }
}
