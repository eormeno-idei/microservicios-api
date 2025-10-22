<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ChannelType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();

            // Información del canal
            $table->string('name');                        // Nombre del canal
            $table->text('description')->nullable();       // Descripción opcional
            $table->enum('type', ChannelType::values());   // Tipo de canal
            $table->text('semantic_context')->nullable();  // Contexto semántico para IA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
