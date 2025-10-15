<?php

use App\Enums\PostType;
use App\Enums\PostStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            // Clave primaria
            $table->id();

            // Relación con usuarios (quien crea el post)
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade'); // Si se elimina el usuario, se eliminan sus posts

            // Contenido del post
            $table->string('name', 255);               // Nombre del post
            $table->text('content');                   // Contenido principal

            // Enums: tipo y estado
            $table->enum('type', PostType::values());
            $table->enum('status', PostStatus::values());

            // Comentarios del moderador
            $table->string('moderator_comments', 100)->nullable();

            // Fechas especiales
            $table->timestamp('scheduled_at')->nullable();  // Cuándo programar
            $table->timestamp('published_at')->nullable();  // Cuándo se publicó realmente
            $table->timestamp('deadline')->nullable();      // Fecha límite
            $table->timestamp('timeout')->nullable();       // Tiempo límite

            // Timestamps automáticos (created_at, updated_at)
            $table->timestamps();
            $table->softDeletes(); // Para borrado suave (deleted_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
