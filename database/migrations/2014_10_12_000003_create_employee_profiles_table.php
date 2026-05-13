<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->date('hire_date')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('eps', 191)->nullable();
            $table->string('arl', 191)->nullable();
            $table->string('emergency_contact', 191)->nullable();
            $table->string('emergency_phone', 32)->nullable();
            $table->string('home_address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('vehicle_type', 64)->nullable();
            $table->string('plate_number', 32)->nullable();
            $table->string('driver_license', 64)->nullable();
            $table->date('license_expiration')->nullable();
            $table->boolean('available')->default(true);
            $table->string('assigned_zone', 191)->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
