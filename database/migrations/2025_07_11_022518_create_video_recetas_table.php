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
        Schema::create('video_recetas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('tipo'); // 'youtube' o 'archivo'
            $table->string('url')->nullable(); // enlace de YouTube
            $table->string('archivo')->nullable(); // nombre del archivo si se sube desde el PC
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_recetas');
    }
};
