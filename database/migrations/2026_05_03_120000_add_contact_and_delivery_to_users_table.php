<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('document_number', 64)->nullable()->after('phone');
            $table->string('company_name', 191)->nullable()->after('document_number');
            $table->string('address_line1', 191)->nullable()->after('company_name');
            $table->string('address_line2', 191)->nullable()->after('address_line1');
            $table->string('city', 120)->nullable()->after('address_line2');
            $table->string('state', 120)->nullable()->after('city');
            $table->string('postal_code', 32)->nullable()->after('state');
            $table->string('country', 2)->nullable()->default('DO')->after('postal_code');
            $table->text('delivery_instructions')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'document_number',
                'company_name',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country',
                'delivery_instructions',
            ]);
        });
    }
};
