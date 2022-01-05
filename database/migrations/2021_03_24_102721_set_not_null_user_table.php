<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetNotNullUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 128)->nullable(false)->change();
            $table->string('middle_name', 128)->nullable(false)->change();
            $table->string('last_name', 128)->nullable(false)->change();
            $table->string('job_title', 256)->nullable(false)->comment('Должность')->change();
            $table->foreignId('organization_id')->nullable(false)->change();
            $table->string('snils', 11)->nullable(false)->change();
            
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
        // TODO
    }
}
