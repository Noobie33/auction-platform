<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            // Approved seller who created this auction listing
            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Listing category
            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            // Basic listing information
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');

            // Item condition: useful for buyer trust and filtering
            $table->enum('condition', ['new', 'used', 'refurbished'])->default('used');

            // Optional item/location information
            $table->string('location')->nullable();

            // Auction pricing
            $table->decimal('starting_price', 12, 2);
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('current_bid', 12, 2)->nullable();
            $table->decimal('min_bid_increment', 12, 2)->default(10.00);

            // Optional fixed-price purchase support for future feature
            $table->decimal('buy_now_price', 12, 2)->nullable();

            // Auction status flow: draft -> active -> ended/failed/cancelled
            $table->enum('status', ['draft', 'active', 'ended', 'cancelled', 'failed'])
                ->default('draft');

            // Auction timing
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Future sniping protection support
            $table->boolean('auto_extend')->default(true);

            // Platform/admin controls
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);

            // Winner user will be filled after auction closes successfully
            $table->foreignId('winner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Soft delete keeps old auction history for dispute/audit purpose
            $table->softDeletes();
            $table->timestamps();

            $table->index('seller_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('ends_at');
            $table->index(['status', 'ends_at']);
            $table->index(['seller_id', 'status']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};