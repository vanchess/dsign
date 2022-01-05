<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMsgCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_msg_category_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique();
            $table->string('title', 128);
            $table->string('description')->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_msg_category', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique();
            $table->string('title', 128);
            $table->string('short_title', 128)->nullable();
            $table->foreignId('type_id')
                   ->constrained('tbl_msg_category_type')
                   ->onUpdate('cascade')
                   ->onDelete('restrict');
            $table->string('description')->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('tbl_msg_category_link', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msg_id')
                   ->constrained('tbl_msg')
                   ->onUpdate('cascade')
                   ->onDelete('cascade');
            $table->foreignId('category_id')
                   ->constrained('tbl_msg_category')
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
        Schema::dropIfExists('tbl_msg_category_link');
        Schema::dropIfExists('tbl_msg_category');
        Schema::dropIfExists('tbl_msg_category_type');
    }
}
