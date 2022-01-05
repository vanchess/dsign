<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToMsgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_msg_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique();
            $table->string('title', 128);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('tbl_msg', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_msg_type');
        Schema::table('tbl_msg', function (Blueprint $table) {
            // TODO
        });
    }
}
