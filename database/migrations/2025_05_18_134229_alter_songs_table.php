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
        Schema::table('songs', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
            $table->string('album_id')->nullable()->change();
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
            $table->string('album_id')->change();
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('CASCADE');
        });
    }
};
