<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lap_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained();
            $table->string('player_guid');
            $table->string('player_name');
            $table->foreignId('bike_id')->constrained();
            $table->integer('lap_no');
            $table->boolean('fastest');
            $table->boolean('invalid');
            $table->decimal('average_speed', 20, 10);
            $table->decimal('lap_time', 20, 10);
            $table->decimal('lap_time_sector_1', 20, 10);
            $table->decimal('lap_time_sector_2', 20, 10);
            $table->decimal('lap_time_sector_3', 20, 10);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lap_times');
    }
};
