<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_organization', function (Blueprint $table) {
            $table->id();
            $table->string('name', 512);
            $table->string('short_name', 128)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('inn', 12)->nullable();
            $table->string('ogrn', 13)->nullable();
            $table->string('kpp', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone', 90)->nullable();
            $table->string('chief', 255)->nullable();
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
        Schema::dropIfExists('tbl_organization');
    }
}