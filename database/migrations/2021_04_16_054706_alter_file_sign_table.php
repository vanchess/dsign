<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFileSignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_file_sign', function (Blueprint $table) {
            $table->foreignId('cert_id')->nullable();
            $table->timestamp('signing_time')->nullable();
            
            $table->foreign('cert_id')->references('id')->on('tbl_crypto_cert')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cert_id']);
            $table->dropColumn(['cert_id','signing_time']);
        });
    }
}
