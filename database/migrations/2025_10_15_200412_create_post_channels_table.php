<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_channels', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained();
            $table->foreignId('channel_id')->constrained();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_channels');
    }
};
