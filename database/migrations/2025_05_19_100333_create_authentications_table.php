<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authentications', function (Blueprint $table) {
            $table->string('access_token', 1024);
            $table->string('refresh_token', 1024);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authentications');
    }
};
