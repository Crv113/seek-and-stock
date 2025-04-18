<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lap_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->string('player_guid');
            $table->string('player_name');
            $table->foreignId('bike_id')->constrained();
            $table->unsignedInteger('average_speed')->nullable();
            $table->unsignedInteger('lap_time');
            $table->unsignedInteger('lap_time_sector_1');
            $table->unsignedInteger('lap_time_sector_2');
            $table->unsignedInteger('lap_time_sector_3');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lap_times');
    }
};
