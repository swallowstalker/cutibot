<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOverlapFlagInHoliday extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("holidays", function (Blueprint $table) {

            $table->integer("overlapped_by")->nullable();

            $table->foreign("overlapped_by", "holiday_overlap")
                ->references("id")->on("holidays")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table("holidays", function (Blueprint $table) {
            $table->dropForeign("holiday_overlap");
        });

        Schema::table("holidays", function (Blueprint $table) {
            $table->dropColumn("overlapped_by");
        });
    }
}
