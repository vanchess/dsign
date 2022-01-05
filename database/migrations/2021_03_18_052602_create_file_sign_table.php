<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileSignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_file_sign', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                   ->constrained('users')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->foreignId('file_id')
                   ->constrained('tbl_files')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->timestamp('verified_on_server_at')->nullable();
            $table->string('verified_on_server_error_srt',512)->nullable();
            $table->boolean('verified_on_server_success')->nullable();
            $table->text('base64');
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
        Schema::dropIfExists('tbl_file_sign');
    }
}
