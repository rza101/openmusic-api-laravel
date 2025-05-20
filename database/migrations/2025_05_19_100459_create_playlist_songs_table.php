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
        Schema::create('playlist_songs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('playlist_id');
            $table->string('song_id');
            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('CASCADE');
            $table->foreign('song_id')->references('id')->on('songs')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_songs');
    }
};
