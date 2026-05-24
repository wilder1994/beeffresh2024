<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meat_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('meat_cut_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('status', 32)->default('available');
            $table->decimal('price_per_kg', 12, 2);
            $table->decimal('price_per_lb', 12, 2);
            $table->decimal('promo_price_kg', 12, 2)->nullable();
            $table->decimal('promo_price_lb', 12, 2)->nullable();
            $table->date('promo_start')->nullable();
            $table->date('promo_end')->nullable();
            $table->decimal('stock', 12, 2)->default(0);
            $table->string('stock_unit', 8)->default('kg');
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->string('sale_type', 32)->default('variable_weight');
            $table->boolean('featured')->default(false);
            $table->boolean('show_on_cinta')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
