<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_medias', function (Blueprint $table) {

            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');


        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_medias');
    }
};
