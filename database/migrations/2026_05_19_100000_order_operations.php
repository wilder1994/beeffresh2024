<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('courier_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('handled_by_user_id')->nullable()->after('courier_id')->constrained('users')->nullOnDelete();
            $table->decimal('shipping_latitude', 10, 7)->nullable()->after('shipping_notes');
            $table->decimal('shipping_longitude', 10, 7)->nullable()->after('shipping_latitude');
            $table->string('payment_method', 32)->default('online_simulated')->after('status');
            $table->unsignedTinyInteger('delivery_attempt')->default(1)->after('payment_method');
            $table->decimal('redelivery_fee', 12, 2)->default(0)->after('delivery_attempt');
            $table->timestamp('assigned_at')->nullable()->after('redelivery_fee');
            $table->timestamp('handled_at')->nullable()->after('assigned_at');
            $table->timestamp('ready_at')->nullable()->after('handled_at');
            $table->timestamp('picked_up_at')->nullable()->after('ready_at');
            $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
            $table->string('tracking_token', 64)->nullable()->unique()->after('delivered_at');

            $table->index(['handled_by_user_id', 'status']);
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });

        Schema::create('order_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index(['courier_id', 'is_active']);
            $table->index(['order_id', 'is_active']);
        });

        Schema::create('courier_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('recorded_at');

            $table->index(['user_id', 'recorded_at']);
        });

        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 16);
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'type']);
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->decimal('store_latitude', 10, 7)->nullable()->after('social_youtube');
            $table->decimal('store_longitude', 10, 7)->nullable()->after('store_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['store_latitude', 'store_longitude']);
        });

        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('courier_locations');
        Schema::dropIfExists('order_assignments');
        Schema::dropIfExists('order_status_logs');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropForeign(['handled_by_user_id']);
            $table->dropColumn([
                'courier_id',
                'handled_by_user_id',
                'shipping_latitude',
                'shipping_longitude',
                'payment_method',
                'delivery_attempt',
                'redelivery_fee',
                'assigned_at',
                'handled_at',
                'ready_at',
                'picked_up_at',
                'delivered_at',
                'tracking_token',
            ]);
        });
    }
};
