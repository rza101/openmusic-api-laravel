<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlist_activities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('playlist_id');
            $table->string('song_id');
            $table->string('user_id');
            $table->string('action');
            $table->dateTime('time');
            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_activities');
    }
};
