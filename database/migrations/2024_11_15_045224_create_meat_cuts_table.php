<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meat_cuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meat_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->unique(['meat_type_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meat_cuts');
    }
};
