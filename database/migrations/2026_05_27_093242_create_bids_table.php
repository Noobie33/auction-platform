<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();

            // Auction listing where the bid is placed
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            // Buyer who placed the bid
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Bid amount; decimal avoids floating point money errors
            $table->decimal('amount', 12, 2);

            // Manual bid or future auto-bid
            $table->boolean('is_auto_bid')->default(false);

            // Fast flag for current highest bid
            $table->boolean('is_winning')->default(false);

            // Audit information for fraud/dispute investigation
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('listing_id');
            $table->index('buyer_id');
            $table->index(['listing_id', 'amount']);
            $table->index(['listing_id', 'is_winning']);
            $table->index(['buyer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};