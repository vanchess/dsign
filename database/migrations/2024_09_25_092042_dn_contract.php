<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DnContract extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_dn_contract', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msg_id')->nullable();
            $table->string('name');
            $table->foreignId('mo_organization_id')
                   ->constrained('tbl_organization')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->string('ogrn', 13)->nullable();
            $table->timestamp('effective_from')->useCurrent();
            $table->timestamp('effective_to')->default('9999-12-31 23:59:59');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('msg_id')->references('id')->on('tbl_msg')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_dn_contract');
    }
}
