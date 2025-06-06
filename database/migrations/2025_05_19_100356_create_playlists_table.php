<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('owner');
            $table->foreign('owner')->references('id')->on('users')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
