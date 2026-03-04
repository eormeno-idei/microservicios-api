<?php

use App\Enums\ChannelType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            // Información del canal
            $table->string('name');                         // Nombre del canal
            $table->text('description')->nullable();        // Descripción opcional
            $table->enum('type', ChannelType::values());    // Tipo de canal
            $table->text('semantic_context')->nullable();   // Contexto semántico del canal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
