<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Displist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_preventive_medical_measure_types', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->nullable();
            $table->string('name', 128)->nullable();
            $table->string('short_name', 16)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tbl_mo', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6);
            $table->string('license', 255)->nullable();
            $table->integer('order');
            $table->foreignId('organization_id')
                   ->constrained('tbl_organization')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->timestamp('inclusion_in_register');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tbl_smo', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5);
            $table->integer('order');
            $table->foreignId('organization_id')
                   ->constrained('tbl_organization')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->timestamp('inclusion_in_register');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tbl_displist_status', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('lable')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tbl_displist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msg_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('msg_id')->references('id')->on('tbl_msg')->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::create('tbl_displist_entry', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 128);
            $table->string('middle_name', 128)->nullable();
            $table->string('last_name', 128);
            $table->timestamp('birthday');
            $table->string('enp', 16);
            $table->string('snils', 11);

            $table->foreignId('preventive_medical_measure_id');
            $table->string('description', 256)->nullable();
            $table->string('contact_info', 256)->nullable();
            $table->foreignId('mo_id')->nullable();
            $table->foreignId('smo_id')->nullable();
            $table->foreignId('insurOgrn')->nullable();
            // Добавить дату предыдущей диспансеризации?
            $table->foreignId('status_id')
                   ->constrained('tbl_displist_status')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->string('status_text', 256)->nullable();
            $table->foreignId('user_id');
            $table->foreignId('organization_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('preventive_medical_measure_id')->references('id')->on('tbl_preventive_medical_measure_types')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('organization_id')->references('id')->on('tbl_organization')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('mo_id')->references('id')->on('tbl_mo')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('smo_id')->references('id')->on('tbl_smo')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_displist_entry');
        Schema::dropIfExists('tbl_displist');
        Schema::dropIfExists('tbl_displist_status');
        Schema::dropIfExists('tbl_smo');
        Schema::dropIfExists('tbl_mo');
        Schema::dropIfExists('tbl_preventive_medical_measures');
    }
}
