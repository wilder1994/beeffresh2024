<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 32);
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->unsignedBigInteger('amount_in_cents');
            $table->string('currency', 3)->default('COP');
            $table->string('status', 32)->index();
            $table->string('payment_method', 64)->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['gateway', 'transaction_id']);
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('status', 32);
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'created_at']);
        });

        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 32);
            $table->string('event_type')->nullable();
            $table->string('idempotency_key')->unique();
            $table->json('payload');
            $table->string('signature')->nullable();
            $table->boolean('checksum_valid')->default(false);
            $table->string('status', 32)->default('received');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
    }
};
