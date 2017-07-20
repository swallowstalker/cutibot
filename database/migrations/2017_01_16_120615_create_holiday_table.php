<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHolidayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("holidays", function (Blueprint $table) {
            $table->integer("id", true);
            $table->text("description");
            $table->date("start");
            $table->date("end");
            $table->timestamps();
        });

        Schema::create("leave_recommendations", function (Blueprint $table) {
            $table->integer("id", true);
            $table->integer("holiday_id");
            $table->date("start");
            $table->date("end");
            $table->timestamps();

            $table->foreign("holiday_id", "leave_to_holiday")
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
        Schema::drop("leave_recommendations");
        Schema::drop("holidays");
    }
}
