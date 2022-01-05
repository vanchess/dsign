<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileSignStampTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_file_sign_stamp_type', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('lable')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_file_sign_stamp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                   ->constrained('tbl_files')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->string('pdf_file_path');
            $table->string('pdf_with_id_file_path')->nullable();
            $table->string('stamped_file_path')->nullable()->unique();
            $table->foreignId('type_id')
                   ->constrained('tbl_file_sign_stamp_type')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['file_id','type_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_file_sign_stamp');
        Schema::dropIfExists('tbl_file_sign_stamp_type');
    }
}
