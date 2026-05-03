<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total', 12, 2);
            $table->string('status', 32)->default('pending');
            $table->string('shipping_recipient_name', 191)->nullable();
            $table->string('shipping_phone', 32)->nullable();
            $table->string('shipping_document_number', 64)->nullable();
            $table->string('shipping_address_line1', 191)->nullable();
            $table->string('shipping_address_line2', 191)->nullable();
            $table->string('shipping_city', 120)->nullable();
            $table->string('shipping_state', 120)->nullable();
            $table->string('shipping_postal_code', 32)->nullable();
            $table->string('shipping_country', 2)->nullable();
            $table->text('shipping_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
