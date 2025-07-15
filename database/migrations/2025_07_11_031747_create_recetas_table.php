<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->enum('tipo', ['youtube', 'archivo']); // 'youtube' o 'archivo'
            $table->string('url')->nullable(); // para enlaces de YouTube
            $table->string('archivo')->nullable(); // para archivos cargados
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
