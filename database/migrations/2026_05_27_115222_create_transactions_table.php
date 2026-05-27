<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Auction/listing and winning bid references
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->restrictOnDelete();

            $table->foreignId('bid_id')
                ->constrained('bids')
                ->restrictOnDelete();

            // Buyer is the winner; seller is the listing owner
            $table->foreignId('buyer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Snapshot of money values at transaction creation time
            $table->decimal('amount', 12, 2);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->decimal('commission_amount', 12, 2)->default(0.00);
            $table->decimal('seller_payout', 12, 2)->default(0.00);

            // Overall transaction lifecycle
            $table->enum('status', [
                'payment_pending',
                'paid',
                'shipped',
                'received',
                'completed',
                'cancelled',
                'refunded',
                'disputed'
            ])->default('payment_pending');

            // Payment information
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded'
            ])->default('pending');

            $table->string('payment_method', 100)->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Shipping information
            $table->enum('shipping_status', [
                'not_required',
                'pending',
                'shipped',
                'delivered'
            ])->default('pending');

            $table->string('shipping_carrier', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();

            // Snapshot of selected address at checkout time
            $table->json('shipping_address')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Receipt and completion
            $table->boolean('buyer_confirmed_receipt')->default(false);
            $table->timestamp('receipt_confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Cancellation/refund info
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 50)->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Admin-only notes
            $table->text('admin_notes')->nullable();

            // Soft delete keeps financial history safe
            $table->softDeletes();
            $table->timestamps();

            $table->index('listing_id');
            $table->index('bid_id');
            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('shipping_status');
            $table->index('created_at');
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};