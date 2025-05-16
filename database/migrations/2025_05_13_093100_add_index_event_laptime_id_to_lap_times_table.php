<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lap_times', function (Blueprint $table) {
            $table->index(['event_id', 'lap_time', 'id'], 'idx_event_laptime_id');
        });
    }

    public function down()
    {
        Schema::table('lap_times', function (Blueprint $table) {
            $table->dropIndex('idx_event_laptime_id');
        });
    }
};
