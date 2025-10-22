<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_medias', function (Blueprint $table) {
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained("medias")->onDelete('cascade');
            $table->primary(['channel_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_medias');
    }
};
