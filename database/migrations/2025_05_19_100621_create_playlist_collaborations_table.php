<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlist_collaborations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('playlist_id');
            $table->string('user_id');
            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_collaborations');
    }
};
