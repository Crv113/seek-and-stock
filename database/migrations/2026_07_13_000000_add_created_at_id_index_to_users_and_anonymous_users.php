<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'idx_users_created_at_id');
        });

        Schema::table('anonymous_users', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'idx_anonymous_users_created_at_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_created_at_id');
        });

        Schema::table('anonymous_users', function (Blueprint $table) {
            $table->dropIndex('idx_anonymous_users_created_at_id');
        });
    }
};
