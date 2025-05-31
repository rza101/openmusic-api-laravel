<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_album_likes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->string('album_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_album_likes');
    }
};
