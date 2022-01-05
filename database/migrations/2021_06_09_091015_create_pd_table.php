<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pd', function (Blueprint $table) {
            $table->id();
            $table->integer('id_');
            $table->string('invite',10)->unique();
            $table->string('first_name', 128)->nullable();
            $table->string('middle_name', 128)->nullable();
            $table->string('last_name', 128)->nullable();
            $table->string('address', 512)->nullable();
            $table->string('snils', 11)->nullable();
            $table->string('p_series', 4)->nullable();
            $table->string('p_number', 6)->nullable();
            $table->string('p_issued_by', 512)->nullable();
            $table->string('p_department_code', 7)->nullable();
            $table->timestamp('p_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pd');
    }
}
