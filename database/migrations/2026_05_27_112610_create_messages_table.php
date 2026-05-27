<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Listing related to this buyer-seller conversation
            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            // User who sends the message
            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // User who receives the message
            $table->foreignId('receiver_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Message body
            $table->text('body');

            // Null means unread by receiver
            $table->timestamp('read_at')->nullable();

            // Allows basic moderation/hiding without deleting record instantly
            $table->softDeletes();

            $table->timestamps();

            $table->index('listing_id');
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index(['listing_id', 'sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};