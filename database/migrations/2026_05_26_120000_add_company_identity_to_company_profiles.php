<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('legal_name', 191)->nullable()->after('id');
            $table->string('trade_name', 191)->nullable()->after('legal_name');
            $table->string('nit', 64)->nullable()->after('trade_name');
            $table->string('contact_phone', 32)->nullable()->after('nit');
            $table->string('contact_email', 191)->nullable()->after('contact_phone');
            $table->string('store_address', 255)->nullable()->after('contact_email');
            $table->string('store_neighborhood', 120)->nullable()->after('store_address');
            $table->string('store_city', 120)->nullable()->after('store_neighborhood');
            $table->string('store_state', 120)->nullable()->after('store_city');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'trade_name',
                'nit',
                'contact_phone',
                'contact_email',
                'store_address',
                'store_neighborhood',
                'store_city',
                'store_state',
            ]);
        });
    }
};
