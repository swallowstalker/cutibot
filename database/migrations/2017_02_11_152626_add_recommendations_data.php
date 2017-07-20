<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecommendationsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("holidays", function (Blueprint $table) {

            $table->dropForeign("holiday_overlap");
        });

        Schema::table("holidays", function (Blueprint $table) {

            $table->dropColumn("overlapped_by");
            $table->dropColumn("long_weekend");
        });

        Schema::table("holidays", function (Blueprint $table) {

            $table->date("recommendation_start")->nullable();
            $table->date("recommendation_end")->nullable();
        });

        Schema::table("leave_recommendations", function (Blueprint $table) {

            $table->dropColumn("end");
            $table->renameColumn("start", "leave_date");
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

            $table->boolean("long_weekend");
            $table->integer("overlapped_by")->nullable();

            $table->foreign("overlapped_by", "holiday_overlap")
                ->references("id")->on("holidays")
                ->onUpdate("CASCADE")->onDelete("CASCADE");
        });

        Schema::table("holidays", function (Blueprint $table) {

            $table->dropColumn("recommendation_start");
            $table->dropColumn("recommendation_end");
        });

        Schema::table("leave_recommendations", function (Blueprint $table) {

            $table->date("end");
            $table->renameColumn("leave_date", "start");
        });
    }
}
