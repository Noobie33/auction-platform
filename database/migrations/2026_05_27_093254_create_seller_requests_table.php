<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_requests', function (Blueprint $table) {
            $table->id();

            // Buyer who applied to become a seller
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Application text written by the buyer
            $table->text('motivation');

            // pending = waiting, approved = accepted, rejected = denied
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            // Admin feedback or rejection reason
            $table->text('admin_note')->nullable();

            // Admin who reviewed this seller request
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Review timestamp
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_requests');
    }
};