<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lap_times', function (Blueprint $table) {
            $table->index('player_guid', 'idx_lap_times_player_guid');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('ending_date', 'idx_events_ending_date');
        });
    }

    public function down(): void
    {
        Schema::table('lap_times', function (Blueprint $table) {
            $table->dropIndex('idx_lap_times_player_guid');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('idx_events_ending_date');
        });
    }
};
