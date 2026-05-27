<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();

            // Setting key, example: commission_rate, payment_deadline_days
            $table->string('key', 100)->unique();

            // Stored as text, then app will cast based on type
            $table->text('value');

            // Helps app understand how to read/cast the setting value
            $table->enum('type', ['string', 'integer', 'float', 'boolean', 'json'])
                ->default('string');

            // Admin-friendly explanation
            $table->string('description')->nullable();

            // Admin can disable a setting without deleting it
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('key');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};