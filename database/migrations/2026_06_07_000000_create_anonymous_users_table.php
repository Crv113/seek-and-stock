<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique();
            $table->string('player_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('user_id');
            $table->timestamps();
        });

        DB::statement("
            INSERT INTO anonymous_users (guid, player_name, created_at, updated_at)
            SELECT lt.player_guid, lt.player_name, NOW(), NOW()
            FROM lap_times lt
            INNER JOIN (SELECT MAX(id) as id FROM lap_times GROUP BY player_guid) latest ON latest.id = lt.id
            WHERE lt.player_guid NOT IN (SELECT guid FROM users WHERE guid IS NOT NULL)
            ON DUPLICATE KEY UPDATE player_name = VALUES(player_name), updated_at = NOW()
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_users');
    }
};
