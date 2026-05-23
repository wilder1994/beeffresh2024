<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name', 191)->nullable();
            $table->string('nit', 64)->nullable();
            $table->string('contact_name', 191)->nullable();
            $table->string('business_phone', 32)->nullable();
            $table->string('business_email', 191)->nullable();
            $table->string('business_address', 255)->nullable();
            $table->string('neighborhood', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('country', 2)->nullable()->default('CO');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('account_type', 64)->nullable();
            $table->string('account_number', 64)->nullable();
            $table->unsignedSmallInteger('credit_days')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_profiles');
    }
};
