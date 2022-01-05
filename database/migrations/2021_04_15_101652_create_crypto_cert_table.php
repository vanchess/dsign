<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoCertTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_crypto_cert', function (Blueprint $table) {
            $table->id();
            
            $table->string('thumbprint', 40)->unique();
            $table->string('serial_number', 256);
            
            $table->timestamp('validfrom');
            $table->timestamp('validto');
            
            $table->string('CN', 128)->nullable();
            $table->string('SN', 128)->nullable();
            $table->string('G', 128)->nullable();
            $table->string('T', 256)->nullable();
            $table->string('OU', 128)->nullable();
            $table->string('O', 256)->nullable();
            $table->string('STREET', 256)->nullable();
            $table->string('L', 128)->nullable();
            $table->string('S', 128)->nullable();
            $table->string('C', 128)->nullable();
            $table->string('E', 255)->nullable();
            $table->string('OGRN', 13)->nullable();
            $table->string('SNILS', 11)->nullable();
            $table->string('INN', 12)->nullable();
            $table->string('issuer', 1024)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_crypto_cert');
    }
}
