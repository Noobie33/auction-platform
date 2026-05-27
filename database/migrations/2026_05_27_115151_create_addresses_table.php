<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            // User who owns this delivery/shipping address
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Example: Home, Office, Shop
            $table->string('label', 50)->default('Home');

            // Receiver information for delivery
            $table->string('recipient_name');
            $table->string('phone', 30)->nullable();

            // Address details
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Bangladesh');

            // User's default delivery address
            $table->boolean('is_default')->default(false);

            // Keep old address records safely instead of permanent delete
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};