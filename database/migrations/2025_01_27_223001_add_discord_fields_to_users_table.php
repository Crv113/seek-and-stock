<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('discord_id')->nullable()->unique();
            $table->string('discord_username')->nullable();
            $table->string('discord_avatar')->nullable();
            $table->string('discord_global_name')->nullable();
            $table->string('discord_locale')->nullable();
            $table->string('email')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->uuid('guid')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'discord_id',
                'discord_username',
                'discord_avatar',
                'discord_global_name',
                'discord_locale',
                'guid',
            ]);
        });
    }
};
