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
        Schema::create('songs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->unsignedInteger('year');
            $table->string('performer');
            $table->string('genre');
            $table->unsignedInteger('duration')->nullable();
            $table->string('album_id');
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
