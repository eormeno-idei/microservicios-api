<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MediaType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();

            $table->string('name');                        // Nombre del medio
            $table->enum('type', MediaType::values()); // Tipo de medio
            $table->json('configuration')->nullable();     // Configuración específica (JSON)
            $table->text('semantic_context')->nullable();  // Contexto semántico para IA
            $table->string('url_webhook')->nullable();     // URL para notificaciones
            $table->boolean('is_active')->default(true);   // Medio activo/inactivo

            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medias');
    }
};
