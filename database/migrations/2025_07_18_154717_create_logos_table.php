<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('logos', function (Blueprint $table) {
        $table->id();
        $table->string('tipo'); // p. ej. 'principal' (logo comercial en sidebar/tienda)
        $table->string('imagen'); // nombre de la imagen
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logos');
    }
};
