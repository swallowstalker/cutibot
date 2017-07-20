<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnForHolidayFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("holidays", function (Blueprint $table) {
            $table->boolean("ignored");
            $table->boolean("long_weekend");
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
            $table->dropColumn("ignored");
            $table->dropColumn("long_weekend");
        });
    }
}
