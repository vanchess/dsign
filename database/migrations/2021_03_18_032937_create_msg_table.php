<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_msg_status', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('lable')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_msg', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->comment('Тема сообщения');
            $table->foreignId('user_id')
                   ->constrained('users')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->foreignId('status_id')
                   ->constrained('tbl_msg_status')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->text('text')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_msg_to_users', function (Blueprint $table) {
            $table->foreignId('msg_id')
                   ->constrained('tbl_msg')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->foreignId('user_id')
                   ->constrained('users')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_msg_files', function (Blueprint $table) {
            $table->foreignId('msg_id')
                   ->constrained('tbl_msg')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->foreignId('file_id')
                   ->constrained('tbl_files')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_msg_status_history', function (Blueprint $table) {
            $table->foreignId('msg_id')
                   ->constrained('tbl_msg')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->foreignId('status_id')
                   ->constrained('tbl_msg_status')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
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
        Schema::dropIfExists('tbl_msg_status_history');
        Schema::dropIfExists('tbl_msg_files');
        Schema::dropIfExists('tbl_msg_to_users');
        Schema::dropIfExists('tbl_msg');
        Schema::dropIfExists('tbl_msg_status');
    }
}
