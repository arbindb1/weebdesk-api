<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->unique();
            $table->foreignId('anime_id')->constrained('anime')->cascadeOnDelete();
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->string('role', 20)->nullable();
            $table->unsignedInteger('favorites')->default(0);
            // null = detail not synced yet; [] = synced, no tags matched.
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
