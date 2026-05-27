<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // User who will receive this in-app notification
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Example: outbid, auction_won, seller_approved, payment_required
            $table->string('type', 100);

            // Short notification heading
            $table->string('title');

            // Main notification text
            $table->text('body');

            // Optional dynamic data, example: listing_id, bid_amount, URL
            $table->json('data')->nullable();

            // Null means unread; timestamp means read
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};