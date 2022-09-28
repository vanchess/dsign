<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organization', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable();
            $table->foreign('type_id')->references('id')->on('tbl_organization_type')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_organization', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn(['type_id']);
        });
    }
}
