<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('address', 255)->nullable();
            $table->string('neighborhood', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('address_reference', 255)->nullable();
            $table->text('delivery_notes')->nullable();
            $table->boolean('accepts_promotions')->default(true);
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('postal_code', 32)->nullable();
            $table->string('country', 2)->nullable()->default('DO');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
