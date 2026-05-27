<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();

            // Dispute belongs to a transaction
            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->restrictOnDelete();

            // Buyer or seller who opened the dispute
            $table->foreignId('raised_by')
                ->constrained('users')
                ->restrictOnDelete();

            // Main dispute reason
            $table->enum('reason_category', [
                'item_not_received',
                'item_not_as_described',
                'seller_not_responding',
                'buyer_not_paying',
                'shipping_damage',
                'fraud',
                'other'
            ]);

            // Detailed complaint text
            $table->text('reason_detail');

            // File paths or evidence references, stored as JSON array
            $table->json('evidence_files')->nullable();

            // Dispute lifecycle
            $table->enum('status', [
                'open',
                'under_review',
                'resolved',
                'closed'
            ])->default('open');

            // Admin resolution details
            $table->text('resolution')->nullable();

            // Admin who resolved/closed the dispute
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('resolution_action', 100)->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Keep dispute history safely
            $table->softDeletes();
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('raised_by');
            $table->index('status');
            $table->index('resolved_by');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};