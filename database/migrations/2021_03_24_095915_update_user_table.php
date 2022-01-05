<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 128)->nullable();
            $table->string('middle_name', 128)->nullable();
            $table->string('last_name', 128)->nullable();
            $table->string('job_title', 256)->nullable()->comment('Должность');
            $table->foreignId('organization_id')->nullable();
            $table->string('snils', 11)->nullable()->unique();
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
            $table->dropColumn(['first_name', 'middle_name', 'last_name', 'job_title', 'organization_id', 'snils']);
        });
    }
}
