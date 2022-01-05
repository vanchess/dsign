<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetNotNullTypeColToMsgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_msg', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable(false)->change();
            $table->foreign('type_id')->references('id')->on('tbl_msg_type')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_msg', function (Blueprint $table) {
            // TODO
        });
    }
}
