<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_bids', function (Blueprint $table) {
            $table->id();

            // Auction listing where buyer enabled proxy/auto bidding
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            // Buyer who set maximum auto bid amount
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Maximum amount buyer is willing to bid
            $table->decimal('max_amount', 12, 2);

            // Last amount placed automatically by system for this buyer
            $table->decimal('current_auto_bid_amount', 12, 2)->default(0.00);

            // Active means system can still auto-bid for this buyer
            $table->boolean('is_active')->default(true);

            // Last time this auto-bid was triggered
            $table->timestamp('last_triggered_at')->nullable();

            // Optional buyer note/internal note
            $table->string('note')->nullable();

            $table->timestamps();

            // One auto-bid rule per buyer per listing
            $table->unique(['listing_id', 'buyer_id']);

            $table->index('listing_id');
            $table->index('buyer_id');
            $table->index(['listing_id', 'is_active']);
            $table->index(['buyer_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_bids');
    }
};