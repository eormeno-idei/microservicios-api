<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_channels', function (Blueprint $table) {
            $table->id();
            // Las dos llaves foráneas que conectamos
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');

            // Campos adicionales del pivot (información extra de la relación)

            // Un usuario solo puede estar una vez en cada canal
            $table->unique(['user_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_channels');
    }
};
