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
        Schema::create('temporary_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('component_id');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size'); // bytes
            $table->string('type'); // image, audio, video, document
            $table->json('metadata')->nullable(); // width, height, duration, etc.
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_uploads');
    }
};
