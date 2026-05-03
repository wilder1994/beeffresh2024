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
            $table->string('shipping_recipient_name', 191)->nullable()->after('status');
            $table->string('shipping_phone', 32)->nullable()->after('shipping_recipient_name');
            $table->string('shipping_document_number', 64)->nullable()->after('shipping_phone');
            $table->string('shipping_address_line1', 191)->nullable()->after('shipping_document_number');
            $table->string('shipping_address_line2', 191)->nullable()->after('shipping_address_line1');
            $table->string('shipping_city', 120)->nullable()->after('shipping_address_line2');
            $table->string('shipping_state', 120)->nullable()->after('shipping_city');
            $table->string('shipping_postal_code', 32)->nullable()->after('shipping_state');
            $table->string('shipping_country', 2)->nullable()->after('shipping_postal_code');
            $table->text('shipping_notes')->nullable()->after('shipping_country');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_recipient_name',
                'shipping_phone',
                'shipping_document_number',
                'shipping_address_line1',
                'shipping_address_line2',
                'shipping_city',
                'shipping_state',
                'shipping_postal_code',
                'shipping_country',
                'shipping_notes',
            ]);
        });
    }
};
