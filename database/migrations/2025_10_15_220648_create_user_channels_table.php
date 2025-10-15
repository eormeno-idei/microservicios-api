<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_channels', function (Blueprint $table) {
            $table->id();

            // Las dos claves foráneas que conectamos
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');

            // Campos adicionales del pivot
            $table->boolean('is_approved')->default(false);    // ¿Está aprobado el usuario?
            $table->timestamp('approved_at')->nullable();      // ¿Cuándo fue aprobado?
            $table->foreignId('approved_by')->nullable()->constrained('users'); // ¿Quién lo aprobó?
            $table->timestamps();

            // Un usuario solo puede estar una vez en cada canal
            $table->unique(['user_id', 'channel_id']);

            // Índice para buscar usuarios aprobados de un canal
            $table->index(['channel_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_channels');
    }
};
