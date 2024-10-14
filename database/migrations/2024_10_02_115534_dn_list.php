<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DnList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_dn_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msg_id')->nullable();
            $table->foreignId('contract_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('msg_id')->references('id')->on('tbl_msg')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('contract_id')->references('id')->on('tbl_dn_contract')->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::create('tbl_dn_list_entry', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->increments('order');
            $table->dropPrimary();
            $table->foreignId('dn_list_id');
            $table->string('first_name', 128);
            $table->string('middle_name', 128)->nullable();
            $table->string('last_name', 128);
            $table->date('birthday');
            $table->string('enp', 16);
            $table->string('snils', 11);
            // $table->date('effective_from')->comment('Дата начала диспансерного наблюдения');
            // $table->foreignId('preventive_medical_measure_id');
            $table->string('description', 256)->nullable();
            $table->string('contact_info', 256)->nullable();
            $table->foreignId('mo_id')->nullable();
            $table->foreignId('smo_id')->nullable();
            $table->foreignId('insurOgrn')->nullable();
            $table->foreignId('status_id')
                   ->constrained('tbl_displist_status')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->string('status_text', 256)->nullable();
            $table->foreignId('user_id');
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('preventive_medical_measure_id')->references('id')->on('tbl_preventive_medical_measure_types')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('mo_id')->references('id')->on('tbl_mo')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('smo_id')->references('id')->on('tbl_smo')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('dn_list_id')->references('id')->on('tbl_displist')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_dn_list_entry');
        Schema::dropIfExists('tbl_dn_list');
    }
}
