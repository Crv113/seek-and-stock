<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_users');
    }
};
