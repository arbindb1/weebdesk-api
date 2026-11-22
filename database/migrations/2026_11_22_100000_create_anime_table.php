<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anime', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->unique();
            $table->string('title');
            $table->string('title_english')->nullable();
            $table->string('image_url')->nullable();
            $table->string('type', 20)->nullable();
            $table->unsignedSmallInteger('episodes')->nullable();
            $table->decimal('score', 4, 2)->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->unsignedInteger('popularity')->nullable();
            $table->unsignedInteger('members')->nullable();
            $table->unsignedInteger('favorites')->nullable();
            $table->string('season', 10)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_seasonal')->default(false)->index();
            $table->boolean('is_top')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anime');
    }
};
